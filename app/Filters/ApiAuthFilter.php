<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiAuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $apiKey = $request->getHeaderLine('X-API-Key');
        
        if (empty($apiKey)) {
            return $this->unauthorized('API key is required');
        }

        $apiAuthKeyModel = new \App\Models\ApiAuthKeyModel();
        $result = $apiAuthKeyModel->validateKey($apiKey);

        if (!$result) {
            return $this->unauthorized('Invalid or expired API key');
        }

        // Store the authenticated user in the request
        $request->user = $result['user'];
        $request->authKey = $result['auth_key'];

        // Check scopes if required
        if (!empty($arguments)) {
            $requiredScopes = $arguments;
            $keyScopes = $result['auth_key']['scopes'] ? json_decode($result['auth_key']['scopes'], true) : [];

            if (empty($keyScopes)) {
                // No scopes defined, allow all
                return;
            }

            foreach ($requiredScopes as $scope) {
                if (!in_array($scope, $keyScopes)) {
                    return $this->forbidden('Insufficient permissions. Required scope: ' . $scope);
                }
            }
        }
    }

    /**
     * We don't need to do anything here.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }

    /**
     * Return unauthorized response
     */
    private function unauthorized(string $message): ResponseInterface
    {
        $response = \Config\Services::response();
        return $response->setStatusCode(401)->setJSON([
            'error' => 'Unauthorized',
            'message' => $message,
        ]);
    }

    /**
     * Return forbidden response
     */
    private function forbidden(string $message): ResponseInterface
    {
        $response = \Config\Services::response();
        return $response->setStatusCode(403)->setJSON([
            'error' => 'Forbidden',
            'message' => $message,
        ]);
    }
}
