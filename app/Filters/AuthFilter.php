<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;
use UnexpectedValueException;
use Exception;

class AuthFilter implements FilterInterface
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

        $response = service('response');

        $headerAuthentication = $request->getServer("HTTP_AUTHORIZATION");
        helper('jwt');

        if (!$encodedToken = getJWT($headerAuthentication)) {
            $data = [
                'success' => false,
                'message' => 'Authentication failed, no token found'
            ];
            return $response->setStatusCode(401)->setJSON($data);
        }

        try {
            $decodedToken = decodeJWT($encodedToken);
        } catch (ExpiredException $e) {
            // Handle expired token exception
            $data = [
                'success' => false,
                'message' => 'Authentication failed, token expired'
            ];
            return $response->setStatusCode(401)->setJSON($data);
        } catch (SignatureInvalidException $e) {
            // Handle invalid signature exception
            $data = [
                'success' => false,
                'message' => 'Authentication failed, token signature verification failed'
            ];
            return $response->setStatusCode(401)->setJSON($data);
        } catch (UnexpectedValueException $e) {
            // Handle invalid token format exception
            $data = [
                'success' => false,
                'message' => 'Authentication failed, token has unexpected value or malformed'
            ];
            return $response->setStatusCode(401)->setJSON($data);
        } catch (Exception $e) {
            // Handle invalid json format exception
            $data = [
                'success' => false,
                'message' => 'Authentication failed, invalid token found',
            ];
            return $response->setStatusCode(401)->setJSON($data);
        }

        $usersModel = new UsersModel();
        if (!$usersModel->where('email', $decodedToken->email)->find()) {
            $data = [
                'success' => false,
                'message' => 'Invalid token, user not found'
            ];
            return $response->setStatusCode(404)->setJSON($data);
        } else {
            $exp = $decodedToken->exp;
            if (time() > $exp) {
                $data = [
                    'success' => false,
                    'message' => 'Authentication failed, expired token'
                ];
                return $response->setStatusCode(401)->setJSON($data);
            }
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
