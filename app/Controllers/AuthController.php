<?php

namespace App\Controllers;

use App\Models\UserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Controllers\ResourceBaseController;
use Firebase\JWT\ExpiredException;

class AuthController extends ResourceBaseController
{
    public function login()
    {
        //TODO: Auditory Logs, register intent of fail login, to detect attacks
        //TODO: Rate Limiting, prevent brute force attacks (also in public POSTs)
        $data = $this->request->getJSON(true);

        sleep(1);
        $model = new UserModel();
        if (!$model->validateLogin($data)) {
            return $this->failValidationErrors($model->getErrors());
        }

        // get username or email
        $field = !empty($data['username']) ? 'username' : 'email';
        $value = $data[$field];

        // search user, if exists
        $user = $model->where($field, $value)->first();

        if (!$user || !password_verify($data['password'], $user['password'])) {
            return $this->failValidationErrors(['login' => 'Nombre de usuario o contraseña incorrectos']);
        }

        $expireTime = 86400;
        $token = $this->generateJWT($user, $expireTime);

        $cookieConfig = [
            'name'   => 'app_token',
            'value'  => $token,
            'expire' => $expireTime,//86400, // x 1 day time in seconds
            // 'secure' => true, // true, Cambia a true si usas HTTPS
            'httponly' => true, // true, Previene el acceso a la cookie desde JavaScript
            // 'samesite' => 'None', // Permite el envío de cookies en solicitudes de origen cruzado (Lax por default)
        ];

        // Establece la cookie
        set_cookie($cookieConfig);

        $userData = [
            'id'       => $user['id'],
            'role'     => $user['role'],
            // Puedes agregar más campos si es necesario, pero evita datos sensibles
        ];


        // Configura la cookie
        $cookieUser = [
            'name'   => 'app_user',
            'value'  => json_encode($userData),
            'expire' => $expireTime,
            'secure' => false, // a true si se usa https
            'httponly' => false,
            'samesite' => 'Lax', // Cambia a 'None' si necesitas acceso en solicitudes de origen cruzado
        ];

        // Establece la cookie
        set_cookie($cookieUser);

        return $this->respond([
                'message' => 'Login exitoso',
                //'token' => $token,
                'user'    => [
                    'id'       => $user['id'],
                    'name'     => $user['name'],
                    'username' => $user['username'],
                    'email'    => $user['email'],
                    'role'     => $user['role'],
                ],
        ]);
    }


    public function logout()
    {
        // Configuración para eliminar la cookie
        $cookieConfig = [
            'name'   => 'app_token',
            'value'  => '', // Establece el valor a vacío
            'expire' => time() - 3600, // Establece la expiración en el pasado
            // 'path'   => '/', // Asegúrate de que el path sea el mismo que usaste al crearla
            // 'secure' => false, // Cambia a true si usas HTTPS
            // 'httponly' => true, // Mantén esto en true por seguridad
            // 'samesite' => 'None', // Ajusta según tu configuración
        ];

        // Elimina la cookie
        set_cookie($cookieConfig);

        // Respuesta de éxito
        return service('response')->setJSON([
            'error' => false,
            'message' => 'Logout exitoso.'
        ])->setStatusCode(200);
    }

    public function refreshToken()
    {
        $token = $this->request->getCookie('jwt');

        if (!$token) {
            return $this->failUnauthorized('No token provided.');
        }

        try {
            // Verifica el token (esto depende de tu implementación de JWT)
            $decoded = $this->verifyJWT($token);

            // Aquí puedes verificar si el token está próximo a expirar
            $exp = $decoded->exp; // Asumiendo que el payload tiene un atributo 'exp'
            $current_time = time();

            // Si el token está a menos de 5 minutos de expirar, genera un nuevo token
            if ($exp - $current_time < 300) {
                $user = $this->getUserFromToken($decoded); // Función para obtener el usuario del token
                $newToken = $this->generateJWT($user);

                // Configura la nueva cookie
                $cookieConfig = [
                    'name'   => 'jwt',
                    'value'  => $newToken,
                    'expire' => 86400, // 1 día
                    'secure' => false, // Cambia a true si usas HTTPS
                    'httponly' => false,
                    'samesite' => 'Strict',
                ];

                set_cookie($cookieConfig);

                return $this->respond([
                    'message' => 'Token renovado con éxito.',
                    'token'   => $newToken,
                ]);
            }

            return $this->respond(['message' => 'El token todavía es válido.']);
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token inválido o expirado.');
        }
    }

    public function getUserFromToken($decoded)
    {
        // Asumiendo que el payload contiene el ID del usuario
        $userId = $decoded->sub; // sub es un estándar en JWT para el sujeto (user ID)

        // Busca el usuario en la base de datos
        $model = new UserModel();
        return $model->find($userId);
    }

    public function verifyJWT($token)
    {
        $key = getenv('JWT_SECRET'); // Asegúrate de tener tu clave secreta en .env

        try {
            // Decodifica el token
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            return $decoded; // Retorna el payload decodificado
        } catch (ExpiredException $e) {
            throw new \Exception('El token ha expirado.');
        } catch (\Exception $e) {
            throw new \Exception('Token inválido.');
        }
    }
    protected function generateJWT(array $user, int $exp = 3600): string
    {
        $key = getenv('JWT_SECRET');
        $payload = [
            'id' => $user['id'],
            'role' => $user['role'],
            // 'iss' => 'http://domain.com', // Emisor del token
            // 'aud' => 'http://domain.com', // Audiencia del token
            'iat' => time(),
            'exp' => time() + $exp, // 1 hour
            'data' => [
                // 'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                // 'role'     => $user['role'],
            ],
        ];
        return JWT::encode($payload, $key, 'HS256');
    }
}