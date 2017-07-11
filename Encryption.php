<?php
/**
 * Cryptography
 * Description:- This class will encrypt the data using PHP and decrypt using MySQL AES_DECRYPT or vice-versa
 */
class Encryption
{
	protected $KEY;

	function __constructor()
	{
		$this->$KEY = 'THIS_IS_THE_BIGGEST_KEY_WHICH_WOULD_BE_AT_REST';
	}

	function mysql_aes_key($key)
	{
		$new_key = str_repeat(chr(0), 16);
		for($i=0,$len=strlen($key);$i<$len;$i++)
		{
			$new_key[$i%16] = $new_key[$i%16] ^ $key[$i];
		}
		return $new_key;
	}

	function aes_encrypt($val)
	{
	    $key = $this->mysql_aes_key($this->KEY);
	    $pad_value = 16-(strlen($val) % 16);
	    $val = str_pad($val, (16*(floor(strlen($val) / 16)+1)), chr($pad_value));
	    return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $val, MCRYPT_MODE_ECB, mcrypt_create_iv( mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_DEV_URANDOM));
	}

	function aes_decrypt_modified($val)
	{
	    $key = $this->mysql_aes_key($this->KEY);
	    $val = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $val, MCRYPT_MODE_ECB, mcrypt_create_iv( mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_DEV_URANDOM));
	    return rtrim($val, "\x00..\x10");
	}

	public function cryptography()
	{
		$encrypt_the_string = 'This is very big string for very big encryption';
		echo $encrypt_the_string;
		echo "<br><br>";

		// Group 1
		$a = $this->aes_encrypt('test');
		echo "\n -------- Encrypted - PHP ----------\n";
		echo $a;
		echo "<br>";
		echo base64_encode($a);

		$query = "SELECT AES_ENCRYPT('test', '".$this->KEY."') AS enc";
		$result = DB::query(Database::SELECT, $query)->execute()->as_array();
		$b = $result[0]['enc'];
		echo "\n -------- Encrypted - MySQL ----------\n";
		echo $b;
		echo "<br><br>";
		echo base64_encode($b);


		// Group 2
		$q = "SELECT AES_DECRYPT('".$a."', '".$this->KEY."') AS decc";
		$r = DB::query(Database::SELECT, $q)->execute()->as_array();
		$c = $r[0]['decc'];

		echo "<br><br>";
		echo "\n -------- Decrypted - MySQL ----------\n";
		echo $c;

		$d = $this->aes_decrypt_modified($b);

		echo "\n -------- Decrypted - PHP ----------\n";
		echo $d;
		echo "<br><br>";

		// Comparison
		var_dump($a===$b);
		var_dump($c===$d);
	}