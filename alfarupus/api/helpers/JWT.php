<?php

namespace Helpers;

class JWT
{

	private $secret;
	private $payload;
	private $header;
	private $signature;

	public $JWT;

	public $iss; //Emissor do token;
	public $aud; //Destinatário do token, representa a aplicação que irá usá-lo.
	public $sub; //Entidade à quem o token pertence, normalmente o ID do usuário;
	public $name; //nome do usuário
	public $email; //email do usuário
	public $iat; //Timestamp de quando o token foi criado;


	public function __construct()
	{
		$this->secret = '123';
	}


	public function create()
	{
		//se a payload, header ou signature estiver vazio cancela a criação da JWT
		if (empty($this->payload) || empty($this->header) || empty($this->signature)) {
			throw new \Exception('Erro ao gerar JWT!', 1);
		}

		$JWT = $this->header . '.' . $this->payload . '.' . $this->signature;
		$this->JWT = $JWT;

		return $this;
	}


	public function generateHeader()
	{
		$header = json_encode(array('typ' => 'JWT', 'alg' => 'HS256'));

		$this->header = $this->base64url_encode($header);

		return $this;
	}


	public function generateSignature()
	{
		$signature = hash_hmac('sha256', $this->header . '.' . $this->payload, md5(md5($this->secret) . md5($this->iat)), true);

		$this->signature = $this->base64url_encode($signature);

		return $this;
	}

	//gera a payload com os atributos da classe
	public function generatePayload()
	{
		$payload = [
			'iss'   => $this->iss,
			'aud'   => $this->aud,
			'sub'   => $this->sub,
			'name'  => $this->name,
			'email' => $this->email,
			'iat'   => $this->iat
		];

		$this->payload = $this->base64url_encode(json_encode($payload));

		return $this;
	}

	//seta os atributos da classe pra gerar a payload
	public function setPayloadData($payload)
	{
		$this->iss   = $payload['iss'];
		$this->aud   = $payload['aud'];
		$this->sub   = $payload['sub'];
		$this->name  = $payload['name'];
		$this->email = $payload['email'];
		$this->iat 	 = $payload['iat'];

		return $this;
	}


	//abre a seta os atributos da JWT
	public function openJWT($JWT)
	{
		$JWT_split = explode('.', $JWT);

		if (count($JWT_split) == 3) {

			$array = json_decode($this->base64url_decode($JWT_split[1]), true);

			$this->JWT = $JWT;

			if (!empty($array) && gettype($array) == 'array') {
				$this->setPayloadData([
					'iss' => $array['iss'],
					'aud' => $array['aud'],
					'sub' => $array['sub'],
					'name' => $array['name'],
					'email' => $array['email'],
					'iat' => $array['iat']
				]);

				$this->generateHeader()->generatePayload()->generateSignature();
			} else {
				throw new \Exception('JWT inválida!', 1);
			}
		} else {
			throw new \Exception('JWT inválida!', 1);
		}

		return $this;
	}


	public function validateJWT($userData)
	{
		try {
			$classJWT = new JWT();

			$classJWT->setPayloadData([
				'iss'   => 'localhost',
				'aud'   => $userData['token'],
				'sub'   => $userData['id'],
				'name'  => $userData['name'],
				'email' => $userData['email'],
				'iat'   => (int) $userData['iat']
			]);

			$classJWT->generatePayload()->generateHeader()->generateSignature();

			//se a payload, header ou signature estiver vazio cancela a criação da JWT
			if (empty($classJWT->payload) || empty($classJWT->header) || empty($classJWT->signature)) {
				throw new \Exception('JWT inválida!', 1);
			}

			$JWTdb = $classJWT->create()->JWT;

			if ($this->JWT == $JWTdb) {
				return json_decode($this->base64url_decode($classJWT->payload), true);
			} else {
				throw new \Exception('JWT inválida ou expirada!', 1);
			}
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), 1);
		}
	}

	public function getDataJWT()
	{
		$data = [
			'user_id' => $this->sub,
			'name'    => $this->name,
			'email'   => $this->email
		];
		return $data;
	}


	private function base64url_encode($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	private function base64url_decode($data)
	{
		return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
	}
}