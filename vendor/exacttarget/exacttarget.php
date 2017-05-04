<?php
/*
	xmlseclibs.php pasted below
	//require('xmlseclibs.php');
 **/
 /*
 Functions to generate simple cases of Exclusive Canonical XML - Callable function is C14NGeneral()
 i.e.: $canonical = C14NGeneral($domelement, TRUE);
 */

 /* helper function */
 function sortAndAddAttrs($element, $arAtts) {
    $newAtts = array();
    foreach ($arAtts AS $attnode) {
       $newAtts[$attnode->nodeName] = $attnode;
    }
    ksort($newAtts);
    foreach ($newAtts as $attnode) {
       $element->setAttribute($attnode->nodeName, $attnode->nodeValue);
    }
 }

 /* helper function */
 function canonical($tree, $element, $withcomments) {
 	if ($tree->nodeType != XML_DOCUMENT_NODE) {
 		$dom = $tree->ownerDocument;
 	} else {
 		$dom = $tree;
 	}
 	if ($element->nodeType != XML_ELEMENT_NODE) {
 		if ($element->nodeType == XML_COMMENT_NODE && ! $withcomments) {
 			return;
 		}
 		$tree->appendChild($dom->importNode($element, TRUE));
 		return;
 	}
 	$arNS = array();
 	if ($element->namespaceURI != "") {
 		if ($element->prefix == "") {
 			$elCopy = $dom->createElementNS($element->namespaceURI, $element->nodeName);
 		} else {
 			$prefix = $tree->lookupPrefix($element->namespaceURI);
 			if ($prefix == $element->prefix) {
 				$elCopy = $dom->createElementNS($element->namespaceURI, $element->nodeName);
 			} else {
 				$elCopy = $dom->createElement($element->nodeName);
 				$arNS[$element->namespaceURI] = $element->prefix;
 			}
 		}
 	} else {
 		$elCopy = $dom->createElement($element->nodeName);
 	}
 	$tree->appendChild($elCopy);

 	/* Create DOMXPath based on original document */
 	$xPath = new DOMXPath($element->ownerDocument);

 	/* Get namespaced attributes */
 	$arAtts = $xPath->query('attribute::*[namespace-uri(.) != ""]', $element);

 	/* Create an array with namespace URIs as keys, and sort them */
 	foreach ($arAtts AS $attnode) {
 		if (array_key_exists($attnode->namespaceURI, $arNS) &&
 			($arNS[$attnode->namespaceURI] == $attnode->prefix)) {
 			continue;
 		}
 		$prefix = $tree->lookupPrefix($attnode->namespaceURI);
 		if ($prefix != $attnode->prefix) {
 		   $arNS[$attnode->namespaceURI] = $attnode->prefix;
 		} else {
 			$arNS[$attnode->namespaceURI] = NULL;
 		}
 	}
 	if (count($arNS) > 0) {
 		asort($arNS);
 	}

 	/* Add namespace nodes */
 	foreach ($arNS AS $namespaceURI=>$prefix) {
 		if ($prefix != NULL) {
 	      	$elCopy->setAttributeNS("http://www.w3.org/2000/xmlns/",
                                "xmlns:".$prefix, $namespaceURI);
 		}
 	}
 	if (count($arNS) > 0) {
 		ksort($arNS);
 	}

 	/* Get attributes not in a namespace, and then sort and add them */
 	$arAtts = $xPath->query('attribute::*[namespace-uri(.) = ""]', $element);
 	sortAndAddAttrs($elCopy, $arAtts);

 	/* Loop through the URIs, and then sort and add attributes within that namespace */
 	foreach ($arNS as $nsURI=>$prefix) {
 	   $arAtts = $xPath->query('attribute::*[namespace-uri(.) = "'.$nsURI.'"]', $element);
 	   sortAndAddAttrs($elCopy, $arAtts);
 	}

 	foreach ($element->childNodes AS $node) {
 		canonical($elCopy, $node, $withcomments);
 	}
 }

 /*
 $element - DOMElement for which to produce the canonical version of
 $exclusive - boolean to indicate exclusive canonicalization (must pass TRUE)
 $withcomments - boolean indicating wether or not to include comments in canonicalized form
 */
 function C14NGeneral($element, $exclusive=FALSE, $withcomments=FALSE) {
 	/* IF PHP 5.2+ then use built in canonical functionality */
 	$php_version = explode('.', PHP_VERSION);
 	if (($php_version[0] > 5) || ($php_version[0] == 5 && $php_version[1] >= 2) ) {
 		return $element->C14N($exclusive, $withcomments);
 	}

 	/* Must be element */
 	if (! $element instanceof DOMElement) {
 		return NULL;
 	}
 	/* Currently only exclusive XML is supported */
 	if ($exclusive == FALSE) {
 		throw new Exception("Only exclusive canonicalization is supported in this version of PHP");
 	}

 	$copyDoc = new DOMDocument();
 	canonical($copyDoc, $element, $withcomments);
 	return $copyDoc->saveXML($copyDoc->documentElement, LIBXML_NOEMPTYTAG);
 }

 class XMLSecurityKey {
 	const TRIPLEDES_CBC = 'http://www.w3.org/2001/04/xmlenc#tripledes-cbc';
 	const AES128_CBC = 'http://www.w3.org/2001/04/xmlenc#aes128-cbc';
 	const AES192_CBC = 'http://www.w3.org/2001/04/xmlenc#aes192-cbc';
 	const AES256_CBC = 'http://www.w3.org/2001/04/xmlenc#aes256-cbc';
 	const RSA_1_5 = 'http://www.w3.org/2001/04/xmlenc#rsa-1_5';
 	const RSA_OAEP_MGF1P = 'http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p';
 	const RSA_SHA1 = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';
 	const DSA_SHA1 = 'http://www.w3.org/2000/09/xmldsig#dsa-sha1';

 	private $cryptParams = array();
 	public $type = 0;
 	public $key = NULL;
 	public $passphrase = "";
 	public $iv = NULL;
 	public $name = NULL;
 	public $keyChain = NULL;
 	public $isEncrypted = FALSE;
 	public $encryptedCtx = NULL;
 	public $guid = NULL;

 	public function __construct($type, $params=NULL) {
 		switch ($type) {
 			case (XMLSecurityKey::TRIPLEDES_CBC):
 				$this->cryptParams['library'] = 'mcrypt';
 				$this->cryptParams['cipher'] = MCRYPT_TRIPLEDES;
 				$this->cryptParams['mode'] = MCRYPT_MODE_CBC;
 				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#tripledes-cbc';
 				break;
 			case (XMLSecurityKey::AES128_CBC):
 				$this->cryptParams['library'] = 'mcrypt';
 				$this->cryptParams['cipher'] = MCRYPT_RIJNDAEL_128;
 				$this->cryptParams['mode'] = MCRYPT_MODE_CBC;
 				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#aes128-cbc';
 				break;
 			case (XMLSecurityKey::AES192_CBC):
 				$this->cryptParams['library'] = 'mcrypt';
 				$this->cryptParams['cipher'] = MCRYPT_RIJNDAEL_128;
 				$this->cryptParams['mode'] = MCRYPT_MODE_CBC;
 				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#aes192-cbc';
 				break;
 			case (XMLSecurityKey::AES256_CBC):
 				$this->cryptParams['library'] = 'mcrypt';
 				$this->cryptParams['cipher'] = MCRYPT_RIJNDAEL_128;
 				$this->cryptParams['mode'] = MCRYPT_MODE_CBC;
 				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#aes256-cbc';
 				break;
 			case (XMLSecurityKey::RSA_1_5):
 				$this->cryptParams['library'] = 'openssl';
 				$this->cryptParams['padding'] = OPENSSL_PKCS1_PADDING;
 				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#rsa-1_5';
 				if (is_array($params) && ! empty($params['type'])) {
 					if ($params['type'] == 'public' || $params['type'] == 'private') {
 						$this->cryptParams['type'] = $params['type'];
 						break;
 					}
 				}
 				throw new Exception('Certificate "type" (private/public) must be passed via parameters');
 				return;
 			case (XMLSecurityKey::RSA_OAEP_MGF1P):
 				$this->cryptParams['library'] = 'openssl';
 				$this->cryptParams['padding'] = OPENSSL_PKCS1_OAEP_PADDING;
 				$this->cryptParams['method'] = 'http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p';
 				$this->cryptParams['hash'] = NULL;
 				if (is_array($params) && ! empty($params['type'])) {
 					if ($params['type'] == 'public' || $params['type'] == 'private') {
 						$this->cryptParams['type'] = $params['type'];
 						break;
 					}
 				}
 				throw new Exception('Certificate "type" (private/public) must be passed via parameters');
 				return;
 			case (XMLSecurityKey::RSA_SHA1):
 				$this->cryptParams['library'] = 'openssl';
 				$this->cryptParams['method'] = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';
 				if (is_array($params) && ! empty($params['type'])) {
 					if ($params['type'] == 'public' || $params['type'] == 'private') {
 						$this->cryptParams['type'] = $params['type'];
 						break;
 					}
 				}
 				throw new Exception('Certificate "type" (private/public) must be passed via parameters');
 				break;
 			default:
 				throw new Exception('Invalid Key Type');
 				return;
 		}
 		$this->type = $type;
 	}

 	public function generateSessionKey() {
 		$key = '';
 		if (! empty($this->cryptParams['cipher']) && ! empty($this->cryptParams['mode'])) {
 			$keysize = mcrypt_module_get_algo_key_size($this->cryptParams['cipher']);
 			/* Generating random key using iv generation routines */
 			if (($keysize > 0) && ($td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '',$this->cryptParams['mode'], ''))) {
 				if ($this->cryptParams['cipher'] == MCRYPT_RIJNDAEL_128) {
 					$keysize = 16;
 					if ($this->type == XMLSecurityKey::AES256_CBC) {
 						$keysize = 32;
 					} elseif ($this->type == XMLSecurityKey::AES192_CBC) {
 						$keysize = 24;
 					}
 				}
 				while (strlen($key) < $keysize) {
 					$key .= mcrypt_create_iv(mcrypt_enc_get_iv_size ($td),MCRYPT_RAND);
 				}
 				mcrypt_module_close($td);
 				$key = substr($key, 0, $keysize);
 				$this->key = $key;
 			}
 		}
 		return $key;
 	}

 	public function loadKey($key, $isFile=FALSE, $isCert = FALSE) {
 		if ($isFile) {
 			$this->key = file_get_contents($key);
 		} else {
 			$this->key = $key;
 		}
 		if ($isCert) {
 			$this->key = openssl_x509_read($this->key);
 			openssl_x509_export($this->key, $str_cert);
 			$this->key = $str_cert;
 		}
 		if ($this->cryptParams['library'] == 'openssl') {
 			if ($this->cryptParams['type'] == 'public') {
 				$this->key = openssl_get_publickey($this->key);
 			} else {
 				$this->key = openssl_get_privatekey($this->key, $this->passphrase);
 			}
 		} else if ($this->cryptParams['cipher'] == MCRYPT_RIJNDAEL_128) {
 			/* Check key length */
 			switch ($this->type) {
 				case (XMLSecurityKey::AES256_CBC):
 					if (strlen($this->key) < 25) {
 						throw new Exception('Key must contain at least 25 characters for this cipher');
 					}
 					break;
 				case (XMLSecurityKey::AES192_CBC):
 					if (strlen($this->key) < 17) {
 						throw new Exception('Key must contain at least 17 characters for this cipher');
 					}
 					break;
 			}
 		}
 	}

 	private function encryptMcrypt($data) {
     	$td = mcrypt_module_open($this->cryptParams['cipher'], '', $this->cryptParams['mode'], '');
 	    $this->iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
 	    mcrypt_generic_init($td, $this->key, $this->iv);
     	$encrypted_data = $this->iv.mcrypt_generic($td, $data);
 	    mcrypt_generic_deinit($td);
     	mcrypt_module_close($td);
 		return $encrypted_data;
 	}

 	private function decryptMcrypt($data) {
     	$td = mcrypt_module_open($this->cryptParams['cipher'], '', $this->cryptParams['mode'], '');
 		$iv_length = mcrypt_enc_get_iv_size($td);

 		$this->iv = substr($data, 0, $iv_length);
 		$data = substr($data, $iv_length);

 	    mcrypt_generic_init($td, $this->key, $this->iv);
     	$decrypted_data = mdecrypt_generic($td, $data);
 	    mcrypt_generic_deinit($td);
     	mcrypt_module_close($td);
 		if ($this->cryptParams['mode'] == MCRYPT_MODE_CBC) {
 	        $dataLen = strlen($decrypted_data);
 			$paddingLength = substr($decrypted_data, $dataLen - 1, 1);
         	$decrypted_data = substr($decrypted_data, 0, $dataLen - ord($paddingLength));
 		}
 		return $decrypted_data;
 	}

 	private function encryptOpenSSL($data) {
 		if ($this->cryptParams['type'] == 'public') {
 			if (! openssl_public_encrypt($data, $encrypted_data, $this->key, $this->cryptParams['padding'])) {
 				throw new Exception('Failure encrypting Data');
 				return;
 			}
 		} else {
 			if (! openssl_private_encrypt($data, $encrypted_data, $this->key, $this->cryptParams['padding'])) {
 				throw new Exception('Failure encrypting Data');
 				return;
 			}
 		}
 		return $encrypted_data;
 	}

 	private function decryptOpenSSL($data) {
 		if ($this->cryptParams['type'] == 'public') {
 			if (! openssl_public_decrypt($data, $decrypted, $this->key, $this->cryptParams['padding'])) {
 				throw new Exception('Failure decrypting Data');
 				return;
 			}
 		} else {
 			if (! openssl_private_decrypt($data, $decrypted, $this->key, $this->cryptParams['padding'])) {
 				throw new Exception('Failure decrypting Data');
 				return;
 			}
 		}
 		return $decrypted;
 	}

 	private function signOpenSSL($data) {
 		if (! openssl_sign ($data, $signature, $this->key)) {
 			throw new Exception('Failure Signing Data');
 			return;
 		}
 		return $signature;
 	}

 	private function verifyOpenSSL($data, $signature) {
 		return openssl_verify ($data, $signature, $this->key);
 	}

 	public function encryptData($data) {
 		switch ($this->cryptParams['library']) {
 			case 'mcrypt':
 				return $this->encryptMcrypt($data);
 				break;
 			case 'openssl':
 				return $this->encryptOpenSSL($data);
 				break;
 		}
 	}

 	public function decryptData($data) {
 		switch ($this->cryptParams['library']) {
 			case 'mcrypt':
 				return $this->decryptMcrypt($data);
 				break;
 			case 'openssl':
 				return $this->decryptOpenSSL($data);
 				break;
 		}
 	}

 	public function signData($data) {
 		switch ($this->cryptParams['library']) {
 			case 'openssl':
 				return $this->signOpenSSL($data);
 				break;
 		}
 	}

 	public function verifySignature($data, $signature) {
 		switch ($this->cryptParams['library']) {
 			case 'openssl':
 				return $this->verifyOpenSSL($data, $signature);
 				break;
 		}
 	}

 	public function getAlgorith() {
 		return $this->cryptParams['method'];
 	}

 	static function makeAsnSegment($type, $string) {
 		switch ($type){
 			case 0x02:
 				if (ord($string) > 0x7f)
 					$string = chr(0).$string;
 				break;
 			case 0x03:
 				$string = chr(0).$string;
 				break;
 		}

 		$length = strlen($string);

 		if ($length < 128){
 		   $output = sprintf("%c%c%s", $type, $length, $string);
 		} else if ($length < 0x0100){
 		   $output = sprintf("%c%c%c%s", $type, 0x81, $length, $string);
 		} else if ($length < 0x010000) {
 		   $output = sprintf("%c%c%c%c%s", $type, 0x82, $length/0x0100, $length%0x0100, $string);
 		} else {
 			$output = NULL;
 		}
 		return($output);
 	}

 	/* Modulus and Exponent must already be base64 decoded */
 	static function convertRSA($modulus, $exponent) {
 		/* make an ASN publicKeyInfo */
 		$exponentEncoding = XMLSecurityKey::makeAsnSegment(0x02, $exponent);
 		$modulusEncoding = XMLSecurityKey::makeAsnSegment(0x02, $modulus);
 		$sequenceEncoding = XMLSecurityKey:: makeAsnSegment(0x30, $modulusEncoding.$exponentEncoding);
 		$bitstringEncoding = XMLSecurityKey::makeAsnSegment(0x03, $sequenceEncoding);
 		$rsaAlgorithmIdentifier = pack("H*", "300D06092A864886F70D0101010500");
 		$publicKeyInfo = XMLSecurityKey::makeAsnSegment (0x30, $rsaAlgorithmIdentifier.$bitstringEncoding);

 		/* encode the publicKeyInfo in base64 and add PEM brackets */
 		$publicKeyInfoBase64 = base64_encode($publicKeyInfo);
 		$encoding = "-----BEGIN PUBLIC KEY-----\n";
 		$offset = 0;
 		while ($segment=substr($publicKeyInfoBase64, $offset, 64)){
 		   $encoding = $encoding.$segment."\n";
 		   $offset += 64;
 		}
 		return $encoding."-----END PUBLIC KEY-----\n";
 	}

 	public function serializeKey($parent) {

 	}
 }

 class XMLSecurityDSig {
 	const XMLDSIGNS = 'http://www.w3.org/2000/09/xmldsig#';
 	const SHA1 = 'http://www.w3.org/2000/09/xmldsig#sha1';
 	const SHA256 = 'http://www.w3.org/2001/04/xmlenc#sha256';
 	const SHA512 = 'http://www.w3.org/2001/04/xmlenc#sha512';
 	const RIPEMD160 = 'http://www.w3.org/2001/04/xmlenc#ripemd160';

 	const C14N = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
 	const C14N_COMMENTS = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments';
 	const EXC_C14N = 'http://www.w3.org/2001/10/xml-exc-c14n#';
 	const EXC_C14N_COMMENTS = 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments';

 	const template = '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
   <ds:SignedInfo>
     <ds:SignatureMethod />
   </ds:SignedInfo>
 </ds:Signature>';

 	public $sigNode = NULL;
 	public $idKeys = array();
 	public $idNS = array();
 	private $signedInfo = NULL;
 	private $xPathCtx = NULL;
 	private $canonicalMethod = NULL;
 	private $prefix = 'ds';
 	private $searchpfx = 'secdsig';

 	public function __construct() {
 		$sigdoc = new DOMDocument();
 		$sigdoc->loadXML(XMLSecurityDSig::template);
 		$this->sigNode = $sigdoc->documentElement;
 	}

 	private function getXPathObj() {
 		if (empty($this->xPathCtx) && ! empty($this->sigNode)) {
 			$xpath = new DOMXPath($this->sigNode->ownerDocument);
 			$xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
 			$this->xPathCtx = $xpath;
 		}
 		return $this->xPathCtx;
 	}

 	static function generate_GUID($prefix=NULL) {
 		$uuid = md5(uniqid(rand(), true));
 		$guid =  $prefix.substr($uuid,0,8)."-".
 				substr($uuid,8,4)."-".
 				substr($uuid,12,4)."-".
 				substr($uuid,16,4)."-".
 				substr($uuid,20,12);
 		return $guid;
 	}

 	public function locateSignature($objDoc) {
 		if ($objDoc instanceof DOMDocument) {
 			$doc = $objDoc;
 		} else {
 			$doc = $objDoc->ownerDocument;
 		}
 		if ($doc) {
 			$xpath = new DOMXPath($doc);
 			$xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
 			$query = ".//secdsig:Signature";
 			$nodeset = $xpath->query($query, $objDoc);
 			$this->sigNode = $nodeset->item(0);
 			return $this->sigNode;
 		}
 		return NULL;
 	}

 	public function createNewSignNode($name, $value=NULL) {
 		$doc = $this->sigNode->ownerDocument;
 		if (! is_null($value)) {
 			$node = $doc->createElementNS(XMLSecurityDSig::XMLDSIGNS, $this->prefix.':'.$name, $value);
 		} else {
 			$node = $doc->createElementNS(XMLSecurityDSig::XMLDSIGNS, $this->prefix.':'.$name);
 		}
 		return $node;
 	}

 	public function setCanonicalMethod($method) {
 		switch ($method) {
 			case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
 			case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
 			case 'http://www.w3.org/2001/10/xml-exc-c14n#':
 			case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
 				$this->canonicalMethod = $method;
 				break;
 			default:
 				throw new Exception('Invalid Canonical Method');
 		}
 		if ($xpath = $this->getXPathObj()) {
 			$query = './'.$this->searchpfx.':SignedInfo';
 			$nodeset = $xpath->query($query, $this->sigNode);
 			if ($sinfo = $nodeset->item(0)) {
 				$query = './'.$this->searchpfx.'CanonicalizationMethod';
 				$nodeset = $xpath->query($query, $sinfo);
 				if (! ($canonNode = $nodeset->item(0))) {
 					$canonNode = $this->createNewSignNode('CanonicalizationMethod');
 					$sinfo->insertBefore($canonNode, $sinfo->firstChild);
 				}
 				$canonNode->setAttribute('Algorithm', $this->canonicalMethod);
 			}
 		}
 	}

 	private function canonicalizeData($node, $canonicalmethod) {
 		$exclusive = FALSE;
 		$withComments = FALSE;
 		switch ($canonicalmethod) {
 			case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
 				$exclusive = FALSE;
 				$withComments = FALSE;
 				break;
 			case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
 				$withComments = TRUE;
 				break;
 			case 'http://www.w3.org/2001/10/xml-exc-c14n#':
 				$exclusive = TRUE;
 				break;
 			case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
 				$exclusive = TRUE;
 				$withComments = TRUE;
 				break;
 		}
 /* Support PHP versions < 5.2 not containing C14N methods in DOM extension */
 		$php_version = explode('.', PHP_VERSION);
 		if (($php_version[0] < 5) || ($php_version[0] == 5 && $php_version[1] < 2) ) {
 			return C14NGeneral($node, $exclusive, $withComments);
 		}
 		return $node->C14N($exclusive, $withComments);
 	}

 	public function canonicalizeSignedInfo() {

 		$doc = $this->sigNode->ownerDocument;
 		$canonicalmethod = NULL;
 		if ($doc) {
 			$xpath = $this->getXPathObj();
 			$query = "./secdsig:SignedInfo";
 			$nodeset = $xpath->query($query, $this->sigNode);
 			if ($signInfoNode = $nodeset->item(0)) {
 				$query = "./secdsig:CanonicalizationMethod";
 				$nodeset = $xpath->query($query, $signInfoNode);
 				if ($canonNode = $nodeset->item(0)) {
 					$canonicalmethod = $canonNode->getAttribute('Algorithm');
 				}
 				$this->signedInfo = $this->canonicalizeData($signInfoNode, $canonicalmethod);
 				return $this->signedInfo;
 			}
 		}
 		return NULL;
 	}

 	public function calculateDigest ($digestAlgorithm, $data) {
 		switch ($digestAlgorithm) {
 			case XMLSecurityDSig::SHA1:
 				$alg = 'sha1';
 				break;
 			case XMLSecurityDSig::SHA256:
 				$alg = 'sha256';
 				break;
 			case XMLSecurityDSig::SHA512:
 				$alg = 'sha512';
 				break;
 			case XMLSecurityDSig::RIPEMD160:
 				$alg = 'ripemd160';
 				break;
 			default:
 				throw new Exception("Cannot validate digest: Unsupported Algorith <$digestAlgorithm>");
 		}
 		return base64_encode(hash($alg, $data, TRUE));
 	}

 	public function validateDigest($refNode, $data) {
 		$xpath = new DOMXPath($refNode->ownerDocument);
 		$xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
 		$query = 'string(./secdsig:DigestMethod/@Algorithm)';
 		$digestAlgorithm = $xpath->evaluate($query, $refNode);
 		$digValue = $this->calculateDigest($digestAlgorithm, $data);
 		$query = 'string(./secdsig:DigestValue)';
 		$digestValue = $xpath->evaluate($query, $refNode);
 		return ($digValue == $digestValue);
 	}

 	public function processTransforms($refNode, $objData) {
 		$data = $objData;
 		$xpath = new DOMXPath($refNode->ownerDocument);
 		$xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
 		$query = './secdsig:Transforms/secdsig:Transform';
 		$nodelist = $xpath->query($query, $refNode);
 		$canonicalMethod = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
 		foreach ($nodelist AS $transform) {
 			$algorithm = $transform->getAttribute("Algorithm");
 			switch ($algorithm) {
 				case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
 				case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
 				case 'http://www.w3.org/2001/10/xml-exc-c14n#':
 				case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
 					$canonicalMethod = $algorithm;
 					break;
 			}
 		}
 		if ($data instanceof DOMNode) {
 			$data = $this->canonicalizeData($objData, $canonicalMethod);
 		}
 		return $data;
 	}

 	public function processRefNode($refNode) {
 		$dataObject = NULL;
 		if ($uri = $refNode->getAttribute("URI")) {
 			$arUrl = parse_url($uri);
 			if (empty($arUrl['path'])) {
 				if ($identifier = $arUrl['fragment']) {
 					$xPath = new DOMXPath($refNode->ownerDocument);
 					if ($this->idNS && is_array($this->idNS)) {
 						foreach ($this->idNS AS $nspf=>$ns) {
 							$xPath->registerNamespace($nspf, $ns);
 						}
 					}
 					$iDlist = '@Id="'.$identifier.'"';
 					if (is_array($this->idKeys)) {
 						foreach ($this->idKeys AS $idKey) {
 							$iDlist .= " or @$idKey='$identifier'";
 						}
 					}
 					$query = '//*['.$iDlist.']';
 					$dataObject = $xPath->query($query)->item(0);
 				} else {
 					$dataObject = $refNode->ownerDocument;
 				}
 			} else {
 				$dataObject = file_get_contents($arUrl);
 			}
 		} else {
 			$dataObject = $refNode->ownerDocument;
 		}
 		$data = $this->processTransforms($refNode, $dataObject);
 		return $this->validateDigest($refNode, $data);
 	}

 	public function validateReference() {
 		$doc = $this->sigNode->ownerDocument;
 		if (! $doc->isSameNode($this->sigNode)) {
 			$this->sigNode->parentNode->removeChild($this->sigNode);
 		}
 		$xpath = $this->getXPathObj();
 		$query = "./secdsig:SignedInfo/secdsig:Reference";
 		$nodeset = $xpath->query($query, $this->sigNode);
 		if ($nodeset->length == 0) {
 			throw new Exception("Reference nodes not found");
 		}
 		foreach ($nodeset AS $refNode) {
 			if (! $this->processRefNode($refNode)) {
 				throw new Exception("Reference validation failed");
 			}
 		}
 		return TRUE;
 	}

 	private function addRefInternal($sinfoNode, $node, $algorithm, $arTransforms=NULL, $options=NULL) {
 		$prefix = NULL;
 		$prefix_ns = NULL;
 		if (is_array($options)) {
 			$prefix = empty($options['prefix'])?NULL:$options['prefix'];
 			$prefix_ns = empty($options['prefix_ns'])?NULL:$options['prefix_ns'];
 			$id_name = empty($options['id_name'])?'Id':$options['id_name'];
 		}

 		$refNode = $this->createNewSignNode('Reference');
 		$sinfoNode->appendChild($refNode);

 		if ($node instanceof DOMDocument) {
 			$uri = NULL;
 		} else {
 /* Do wer really need to set a prefix? */
 			$uri = XMLSecurityDSig::generate_GUID();
 			$refNode->setAttribute("URI", '#'.$uri);
 		}

 		$transNodes = $this->createNewSignNode('Transforms');
 		$refNode->appendChild($transNodes);

 		if (is_array($arTransforms)) {
 			foreach ($arTransforms AS $transform) {
 				$transNode = $this->createNewSignNode('Transform');
 				$transNodes->appendChild($transNode);
 				$transNode->setAttribute('Algorithm', $transform);
 			}
 		} elseif (! empty($this->canonicalMethod)) {
 			$transNode = $this->createNewSignNode('Transform');
 			$transNodes->appendChild($transNode);
 			$transNode->setAttribute('Algorithm', $this->canonicalMethod);
 		}

 		if (! empty($uri)) {
 			$attname = $id_name;
 			if (! empty($prefix)) {
 				$attname = $prefix.':'.$attname;
 			}
 			$node->setAttributeNS($prefix_ns, $attname, $uri);
 		}

 		$canonicalData = $this->processTransforms($refNode, $node);
 		$digValue = $this->calculateDigest($algorithm, $canonicalData);

 		$digestMethod = $this->createNewSignNode('DigestMethod');
 		$refNode->appendChild($digestMethod);
 		$digestMethod->setAttribute('Algorithm', $algorithm);

 		$digestValue = $this->createNewSignNode('DigestValue', $digValue);
 		$refNode->appendChild($digestValue);
 	}

 	public function addReference($node, $algorithm, $arTransforms=NULL, $options=NULL) {
 		if ($xpath = $this->getXPathObj()) {
 			$query = "./secdsig:SignedInfo";
 			$nodeset = $xpath->query($query, $this->sigNode);
 			if ($sInfo = $nodeset->item(0)) {
 				$this->addRefInternal($sInfo, $node, $algorithm, $arTransforms, $options);
 			}
 		}
 	}

 	public function addReferenceList($arNodes, $algorithm, $arTransforms=NULL, $options=NULL) {
 		if ($xpath = $this->getXPathObj()) {
 			$query = "./secdsig:SignedInfo";
 			$nodeset = $xpath->query($query, $this->sigNode);
 			if ($sInfo = $nodeset->item(0)) {
 				foreach ($arNodes AS $node) {
 					$this->addRefInternal($sInfo, $node, $algorithm, $arTransforms, $options);
 				}
 			}
 		}
 	}

 	public function locateKey($node=NULL) {
 		if (empty($node)) {
 			$node = $this->sigNode;
 		}
 		if (! $node instanceof DOMNode) {
 			return NULL;
 		}
 		if ($doc = $node->ownerDocument) {
 			$xpath = new DOMXPath($doc);
 			$xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
 			$query = "string(./secdsig:SignedInfo/secdsig:SignatureMethod/@Algorithm)";
 			$algorithm = $xpath->evaluate($query, $node);
 			if ($algorithm) {
 				try {
 					$objKey = new XMLSecurityKey($algorithm, array('type'=>'public'));
 				} catch (Exception $e) {
 					return NULL;
 				}
 				return $objKey;
 			}
 		}
 		return NULL;
 	}

 	public function verify($objKey) {
 		$doc = $this->sigNode->ownerDocument;
 		$xpath = new DOMXPath($doc);
 		$xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
 		$query = "string(./secdsig:SignatureValue)";
 		$sigValue = $xpath->evaluate($query, $this->sigNode);
 		if (empty($sigValue)) {
 			throw new Exception("Unable to locate SignatureValue");
 		}
 		return $objKey->verifySignature($this->signedInfo, base64_decode($sigValue));
 	}

 	public function signData($objKey, $data) {
 		return $objKey->signData($data);
 	}

 	public function sign($objKey) {
 		if ($xpath = $this->getXPathObj()) {
 			$query = "./secdsig:SignedInfo";
 			$nodeset = $xpath->query($query, $this->sigNode);
 			if ($sInfo = $nodeset->item(0)) {
 				$query = "./secdsig:SignatureMethod";
 				$nodeset = $xpath->query($query, $sInfo);
 				$sMethod = $nodeset->item(0);
 				$sMethod->setAttribute('Algorithm', $objKey->type);
 				$data = $this->canonicalizeData($sInfo, $this->canonicalMethod);
 				$sigValue = base64_encode($this->signData($objKey, $data));
 				$sigValueNode = $this->createNewSignNode('SignatureValue', $sigValue);
 				if ($infoSibling = $sInfo->nextSibling) {
 					$infoSibling->parentNode->insertBefore($sigValueNode, $infoSibling);
 				} else {
 					$this->sigNode->appendChild($sigValueNode);
 				}
 			}
 		}
 	}

 	public function appendCert() {

 	}

 	public function appendKey($objKey, $parent=NULL) {
 		$objKey->serializeKey($parent);
 	}

 	public function appendSignature($parentNode, $insertBefore = FALSE) {
 		$baseDoc = ($parentNode instanceof DOMDocument)?$parentNode:$parentNode->ownerDocument;
 		$newSig = $baseDoc->importNode($this->sigNode, TRUE);
 		if ($insertBefore) {
 			$parentNode->insertBefore($newSig, $parentNode->firstChild);
 		} else {
 			$parentNode->appendChild($newSig);
 		}
 	}

 	static function get509XCert($cert, $isPEMFormat=TRUE) {
 		if ($isPEMFormat) {
 			$data = '';
 			$arCert = explode("\n", $cert);
 			$inData = FALSE;
 			foreach ($arCert AS $curData) {
 				if (! $inData) {
 					if (strncmp($curData, '-----BEGIN CERTIFICATE', 22) == 0) {
 						$inData = TRUE;
 					}
 				} else {
 					if (strncmp($curData, '-----END CERTIFICATE', 20) == 0) {
 						break;
 					}
 					$data .= trim($curData);
 				}
 			}
 		} else {
 			$data = $cert;
 		}
 		return $data;
 	}

 	public function add509Cert($cert, $isPEMFormat=TRUE) {
 		$data = XMLSecurityDSig::get509XCert($cert, $isPEMFormat);
 		if ($xpath = $this->getXPathObj()) {
 			$query = "./secdsig:KeyInfo";
 			$nodeset = $xpath->query($query, $this->sigNode);
 			$keyInfo = $nodeset->item(0);
 			if (! $keyInfo) {
 				$inserted = FALSE;
 				$keyInfo = $this->createNewSignNode('KeyInfo');
 				if ($xpath = $this->getXPathObj()) {
 					$query = "./secdsig:Object";
 					$nodeset = $xpath->query($query, $this->sigNode);
 					if ($sObject = $nodeset->item(0)) {
 						$sObject->parentNode->insertBefore($keyInfo, $sObject);
 						$inserted = TRUE;
 					}
 				}
 				if (! $inserted) {
 					$this->sigNode->appendChild($keyInfo);
 				}
 			}
 			$x509DataNode = $this->createNewSignNode('X509Data');
 			$keyInfo->appendChild($x509DataNode);
 			$x509CertNode = $this->createNewSignNode('X509Certificate', $data);
 			$x509DataNode->appendChild($x509CertNode);
 		}
 	}
 }

 class XMLSecEnc {
 	const template = "<xenc:EncryptedData xmlns:xenc='http://www.w3.org/2001/04/xmlenc#'>
    <xenc:CipherData>
       <xenc:CipherValue></xenc:CipherValue>
    </xenc:CipherData>
 </xenc:EncryptedData>";

 	const Element = 'http://www.w3.org/2001/04/xmlenc#Element';
 	const Content = 'http://www.w3.org/2001/04/xmlenc#Content';
 	const URI = 3;
 	const XMLENCNS = 'http://www.w3.org/2001/04/xmlenc#';

 	private $encdoc = NULL;
 	private $rawNode = NULL;
 	public $type = NULL;
 	public $encKey = NULL;

 	public function __construct() {
 		$this->encdoc = new DOMDocument();
 		$this->encdoc->loadXML(XMLSecEnc::template);
 	}

 	public function setNode($node) {
 		$this->rawNode = $node;
 	}

 	public function encryptNode($objKey, $replace=TRUE) {
 		$data = '';
 		if (empty($this->rawNode)) {
 			throw new Exception('Node to encrypt has not been set');
 		}
 		if (! $objKey instanceof XMLSecurityKey) {
 			throw new Exception('Invalid Key');
 		}
 		$doc = $this->rawNode->ownerDocument;
 		$xPath = new DOMXPath($this->encdoc);
 		$objList = $xPath->query('/xenc:EncryptedData/xenc:CipherData/xenc:CipherValue');
 		$cipherValue = $objList->item(0);
 		if ($cipherValue == NULL) {
 			throw new Exception('Error locating CipherValue element within template');
 		}
 		switch ($this->type) {
 			case (XMLSecEnc::Element):
 				$data = $doc->saveXML($this->rawNode);
 				$this->encdoc->documentElement->setAttribute('Type', XMLSecEnc::Element);
 				break;
 			case (XMLSecEnc::Content):
 				$children = $this->rawNode->childNodes;
 				foreach ($children AS $child) {
 					$data .= $doc->saveXML($child);
 				}
 				$this->encdoc->documentElement->setAttribute('Type', XMLSecEnc::Content);
 				break;
 			default:
 				throw new Exception('Type is currently not supported');
 				return;
 		}

 		$encMethod = $this->encdoc->documentElement->appendChild($this->encdoc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:EncryptionMethod'));
 		$encMethod->setAttribute('Algorithm', $objKey->getAlgorith());
 		$cipherValue->parentNode->parentNode->insertBefore($encMethod, $cipherValue->parentNode);

 		$strEncrypt = base64_encode($objKey->encryptData($data));
 		$value = $this->encdoc->createTextNode($strEncrypt);
 		$cipherValue->appendChild($value);

 		if ($replace) {
 			switch ($this->type) {
 				case (XMLSecEnc::Element):
 					if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
 						return $this->encdoc;
 					}
 					$importEnc = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, TRUE);
 					$this->rawNode->parentNode->replaceChild($importEnc, $this->rawNode);
 					return $importEnc;
 					break;
 				case (XMLSecEnc::Content):
 					$importEnc = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, TRUE);
 					while($this->rawNode->firstChild) {
 						$this->rawNode->removeChild($this->rawNode->firstChild);
 					}
 					$this->rawNode->appendChild($importEnc);
 					return $importEnc;
 					break;
 			}
 		}
 	}

 	public function decryptNode($objKey, $replace=TRUE) {
 		$data = '';
 		if (empty($this->rawNode)) {
 			throw new Exception('Node to decrypt has not been set');
 		}
 		if (! $objKey instanceof XMLSecurityKey) {
 			throw new Exception('Invalid Key');
 		}
 		$doc = $this->rawNode->ownerDocument;
 		$xPath = new DOMXPath($doc);
 		$xPath->registerNamespace('xmlencr', XMLSecEnc::XMLENCNS);
 		/* Only handles embedded content right now and not a reference */
 		$query = "./xmlencr:CipherData/xmlencr:CipherValue";
 		$nodeset = $xPath->query($query, $this->rawNode);

 		if ($node = $nodeset->item(0)) {
 			$encryptedData = base64_decode($node->nodeValue);
 			$decrypted = $objKey->decryptData($encryptedData);
 			if ($replace) {
 				switch ($this->type) {
 					case (XMLSecEnc::Element):
 						$newdoc = new DOMDocument();
 						$newdoc->loadXML($decrypted);
 						if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
 							return $newdoc;
 						}
 						$importEnc = $this->rawNode->ownerDocument->importNode($newdoc->documentElement, TRUE);
 						$this->rawNode->parentNode->replaceChild($importEnc, $this->rawNode);
 						return $importEnc;
 						break;
 					case (XMLSecEnc::Content):
 						if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
 							$doc = $this->rawNode;
 						} else {
 							$doc = $this->rawNode->ownerDocument;
 						}
 						$newFrag = $doc->createDOMDocumentFragment();
 						$newFrag->appendXML($decrypted);
 						$this->rawNode->parentNode->replaceChild($newFrag, $this->rawNode);
 						return $this->rawNode->parentNode;
 						break;
 					default:
 						return $decrypted;
 				}
 			} else {
 				return $decrypted;
 			}
 		} else {
 			throw new Exception("Cannot locate encrypted data");
 		}
 	}

 	public function encryptKey($srcKey, $rawKey, $append=TRUE) {
 		if ((! $srcKey instanceof XMLSecurityKey) || (! $rawKey instanceof XMLSecurityKey)) {
 			throw new Exception('Invalid Key');
 		}
 		$strEncKey = base64_encode($srcKey->encryptData($rawKey->key));
 		$root = $this->encdoc->documentElement;
 		$encKey = $this->encdoc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:EncryptedKey');
 		if ($append) {
 			$keyInfo = $root->appendChild($this->encdoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'dsig:KeyInfo'));
 			$keyInfo->appendChild($encKey);
 		} else {
 			$this->encKey = $encKey;
 		}
 		$encMethod = $encKey->appendChild($this->encdoc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:EncryptionMethod'));
 		$encMethod->setAttribute('Algorithm', $srcKey->getAlgorith());
 		if (! empty($srcKey->name)) {
 			$keyInfo = $encKey->appendChild($this->encdoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'dsig:KeyInfo'));
 			$keyInfo->appendChild($this->encdoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'dsig:KeyName', $srcKey->name));
 		}
 		$cipherData = $encKey->appendChild($this->encdoc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:CipherData'));
 		$cipherData->appendChild($this->encdoc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:CipherValue', $strEncKey));
 		return;
 	}

 	public function decryptKey($encKey) {
 		if (! $encKey->isEncrypted) {
 			throw new Exception("Key is not Encrypted");
 		}
 		if (empty($encKey->key)) {
 			throw new Exception("Key is missing data to perform the decryption");
 		}
 		return $this->decryptNode($encKey, FALSE);
 	}

 	public function locateEncryptedData($element) {
 		if ($element instanceof DOMDocument) {
 			$doc = $element;
 		} else {
 			$doc = $element->ownerDocument;
 		}
 		if ($doc) {
 			$xpath = new DOMXPath($doc);
 			$query = "//*[local-name()='EncryptedData' and namespace-uri()='".XMLSecEnc::XMLENCNS."']";
 			$nodeset = $xpath->query($query);
 			return $nodeset->item(0);
 		}
 		return NULL;
 	}

 	public function locateKey($node=NULL) {
 		if (empty($node)) {
 			$node = $this->rawNode;
 		}
 		if (! $node instanceof DOMNode) {
 			return NULL;
 		}
 		if ($doc = $node->ownerDocument) {
 			$xpath = new DOMXPath($doc);
 			$xpath->registerNamespace('xmlsecenc', XMLSecEnc::XMLENCNS);
 			$query = ".//xmlsecenc:EncryptionMethod";
 			$nodeset = $xpath->query($query, $node);
 			if ($encmeth = $nodeset->item(0)) {
    				$attrAlgorithm = $encmeth->getAttribute("Algorithm");
 				try {
 					$objKey = new XMLSecurityKey($attrAlgorithm, array('type'=>'private'));
 				} catch (Exception $e) {
 					return NULL;
 				}
 				return $objKey;
 			}
 		}
 		return NULL;
 	}

 	static function staticLocateKeyInfo($objBaseKey=NULL, $node=NULL) {
 		if (empty($node) || (! $node instanceof DOMNode)) {
 			return NULL;
 		}
 		if ($doc = $node->ownerDocument) {
 			$xpath = new DOMXPath($doc);
 			$xpath->registerNamespace('xmlsecenc', XMLSecEnc::XMLENCNS);
 			$xpath->registerNamespace('xmlsecdsig', XMLSecurityDSig::XMLDSIGNS);
 			$query = "./xmlsecdsig:KeyInfo";
 			$nodeset = $xpath->query($query, $node);
 			if ($encmeth = $nodeset->item(0)) {
 				foreach ($encmeth->childNodes AS $child) {
 					switch ($child->localName) {
 						case 'KeyName':
 							if (! empty($objBaseKey)) {
 								$objBaseKey->name = $child->nodeValue;
 							}
 							break;
 						case 'KeyValue':
 							foreach ($child->childNodes AS $keyval) {
 								switch ($keyval->localName) {
 									case 'DSAKeyValue':
 										throw new Exception("DSAKeyValue currently not supported");
 										break;
 									case 'RSAKeyValue':
 										$modulus = NULL;
 										$exponent = NULL;
 										if ($modulusNode = $keyval->getElementsByTagName('Modulus')->item(0)) {
 											$modulus = base64_decode($modulusNode->nodeValue);
 										}
 										if ($exponentNode = $keyval->getElementsByTagName('Exponent')->item(0)) {
 											$exponent = base64_decode($exponentNode->nodeValue);
 										}
 										if (empty($modulus) || empty($exponent)) {
 											throw new Exception("Missing Modulus or Exponent");
 										}
 										$publicKey = XMLSecurityKey::convertRSA($modulus, $exponent);
 										$objBaseKey->loadKey($publicKey);
 										break;
 								}
 							}
 							break;
 						case 'RetrievalMethod':
 							/* Not currently supported */
 							break;
 						case 'EncryptedKey':
 							$objenc = new XMLSecEnc();
 							$objenc->setNode($child);
 							if (! $objKey = $objenc->locateKey()) {
 								throw new Exception("Unable to locate algorithm for this Encrypted Key");
 							}
 							$objKey->isEncrypted = TRUE;
 							$objKey->encryptedCtx = $objenc;
 							XMLSecEnc::staticLocateKeyInfo($objKey, $child);
 							return $objKey;
 							break;
 						case 'X509Data':
 							if ($x509certNodes = $child->getElementsByTagName('X509Certificate')) {
 								if ($x509certNodes->length > 0) {
 									$x509cert = $x509certNodes->item(0)->textContent;
 									$x509cert = str_replace(array("\r", "\n"), "", $x509cert);
 									$x509cert = "-----BEGIN CERTIFICATE-----\n".chunk_split($x509cert, 64, "\n")."-----END CERTIFICATE-----\n";
 									$objBaseKey->loadKey($x509cert);
 								}
 							}
 							break;
 					}
 				}
 			}
 			return $objBaseKey;
 		}
 		return NULL;
 	}

 	public function locateKeyInfo($objBaseKey=NULL, $node=NULL) {
 		if (empty($node)) {
 			$node = $this->rawNode;
 		}
 		return XMLSecEnc::staticLocateKeyInfo($objBaseKey, $node);
 	}
 }
/*
	end xmlseclibs.php
	soap-wsse.php pasted below
	//require('soap-wsse.php');
 **/

class WSSESoap {
	const WSSENS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
	const WSUNS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
	const WSUNAME = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0';
	const WSSEPFX = 'wsse';
	const WSUPFX = 'wsu';
	private $soapNS, $soapPFX;
	private $soapDoc = NULL;
	private $envelope = NULL;
	private $SOAPXPath = NULL;
	private $secNode = NULL;
	public $signAllHeaders = FALSE;

	private function locateSecurityHeader($bMustUnderstand = TRUE, $setActor = NULL) {
		if ($this->secNode == NULL) {
			$headers = $this->SOAPXPath->query('//wssoap:Envelope/wssoap:Header');
			$header = $headers->item(0);
			if (! $header) {
				$header = $this->soapDoc->createElementNS($this->soapNS, $this->soapPFX.':Header');
				$this->envelope->insertBefore($header, $this->envelope->firstChild);
			}
			$secnodes = $this->SOAPXPath->query('./wswsse:Security', $header);
			$secnode = NULL;
			foreach ($secnodes AS $node) {
				$actor = $node->getAttributeNS($this->soapNS, 'actor');
				if ($actor == $setActor) {
					$secnode = $node;
					break;
				}
			}
			if (! $secnode) {
				$secnode = $this->soapDoc->createElementNS(WSSESoap::WSSENS, WSSESoap::WSSEPFX.':Security');
				$header->appendChild($secnode);
				if ($bMustUnderstand) {
					$secnode->setAttributeNS($this->soapNS, $this->soapPFX.':mustUnderstand', '1');
				}
				if (! empty($setActor)) {
					$ename = 'actor';
					if ($this->soapNS == 'http://www.w3.org/2003/05/soap-envelope') {
						$ename = 'role';
					}
					$secnode->setAttributeNS($this->soapNS, $this->soapPFX.':'.$ename, $setActor);
				}
			}
			$this->secNode = $secnode;
		}
		return $this->secNode;
	}

	public function __construct($doc, $bMustUnderstand = TRUE, $setActor=NULL) {
		$this->soapDoc = $doc;
		$this->envelope = $doc->documentElement;
		$this->soapNS = $this->envelope->namespaceURI;
		$this->soapPFX = $this->envelope->prefix;
		$this->SOAPXPath = new DOMXPath($doc);
		$this->SOAPXPath->registerNamespace('wssoap', $this->soapNS);
		$this->SOAPXPath->registerNamespace('wswsse', WSSESoap::WSSENS);
		$this->locateSecurityHeader($bMustUnderstand, $setActor);
	}

	public function addTimestamp($secondsToExpire=3600) {
		/* Add the WSU timestamps */
		$security = $this->locateSecurityHeader();

		$timestamp = $this->soapDoc->createElementNS(WSSESoap::WSUNS, WSSESoap::WSUPFX.':Timestamp');
		$security->insertBefore($timestamp, $security->firstChild);
		$currentTime = time();
		$created = $this->soapDoc->createElementNS(WSSESoap::WSUNS,  WSSESoap::WSUPFX.':Created', gmdate("Y-m-d\TH:i:s", $currentTime).'Z');
		$timestamp->appendChild($created);
		if (! is_null($secondsToExpire)) {
			$expire = $this->soapDoc->createElementNS(WSSESoap::WSUNS,  WSSESoap::WSUPFX.':Expires', gmdate("Y-m-d\TH:i:s", $currentTime + $secondsToExpire).'Z');
			$timestamp->appendChild($expire);
		}
	}

	public function addUserToken($userName, $password=NULL, $passwordDigest=FALSE) {
		if ($passwordDigest && empty($password)) {
			throw new Exception("Cannot calculate the digest without a password");
		}

		$security = $this->locateSecurityHeader();

		$token = $this->soapDoc->createElementNS(WSSESoap::WSSENS, WSSESoap::WSSEPFX.':UsernameToken');
		$security->insertBefore($token, $security->firstChild);

		$username = $this->soapDoc->createElementNS(WSSESoap::WSSENS,  WSSESoap::WSSEPFX.':Username', $userName);
		$token->appendChild($username);

		/* Generate nonce - create a 256 bit session key to be used */
		$objKey = new XMLSecurityKey(XMLSecurityKey::AES256_CBC);
		$nonce = $objKey->generateSessionKey();
		unset($objKey);
		$createdate = gmdate("Y-m-d\TH:i:s").'Z';

		if ($password) {
			$passType = WSSESoap::WSUNAME.'#PasswordText';
			if ($passwordDigest) {
				$password = base64_encode(sha1($nonce.$createdate. $password, true));
				$passType = WSSESoap::WSUNAME.'#PasswordDigest';
			}
			$passwordNode = $this->soapDoc->createElementNS(WSSESoap::WSSENS,  WSSESoap::WSSEPFX.':Password', $password);
			$token->appendChild($passwordNode);
			$passwordNode->setAttribute('Type', $passType);
		}

		$nonceNode = $this->soapDoc->createElementNS(WSSESoap::WSSENS,  WSSESoap::WSSEPFX.':Nonce', base64_encode($nonce));
		$token->appendChild($nonceNode);

		$created = $this->soapDoc->createElementNS(WSSESoap::WSUNS,  WSSESoap::WSUPFX.':Created', $createdate);
		$token->appendChild($created);
	}

	public function addBinaryToken($cert, $isPEMFormat=TRUE, $isDSig=TRUE) {
		$security = $this->locateSecurityHeader();
		$data = XMLSecurityDSig::get509XCert($cert, $isPEMFormat);

		$token = $this->soapDoc->createElementNS(WSSESoap::WSSENS, WSSESoap::WSSEPFX.':BinarySecurityToken', $data);
		$security->insertBefore($token, $security->firstChild);

		$token->setAttribute('EncodingType', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary');
		$token->setAttributeNS(WSSESoap::WSUNS, WSSESoap::WSUPFX.':Id', XMLSecurityDSig::generate_GUID());
		$token->setAttribute('ValueType', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3');

		return $token;
	}

	public function attachTokentoSig($token) {
		if (! ($token instanceof DOMElement)) {
			throw new Exception('Invalid parameter: BinarySecurityToken element expected');
		}
		$objXMLSecDSig = new XMLSecurityDSig();
		if ($objDSig = $objXMLSecDSig->locateSignature($this->soapDoc)) {
			$tokenURI = '#'.$token->getAttributeNS(WSSESoap::WSUNS, "Id");
			$this->SOAPXPath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
			$query = "./secdsig:KeyInfo";
			$nodeset = $this->SOAPXPath->query($query, $objDSig);
			$keyInfo = $nodeset->item(0);
			if (! $keyInfo) {
				$keyInfo = $objXMLSecDSig->createNewSignNode('KeyInfo');
				$objDSig->appendChild($keyInfo);
			}

			$tokenRef = $this->soapDoc->createElementNS(WSSESoap::WSSENS, WSSESoap::WSSEPFX.':SecurityTokenReference');
			$keyInfo->appendChild($tokenRef);
			$reference = $this->soapDoc->createElementNS(WSSESoap::WSSENS, WSSESoap::WSSEPFX.':Reference');
			$reference->setAttribute("URI", $tokenURI);
			$tokenRef->appendChild($reference);
		} else {
			throw new Exception('Unable to locate digital signature');
		}
	}

	public function signSoapDoc($objKey) {
		$objDSig = new XMLSecurityDSig();

		$objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

		$arNodes = array();
		foreach ($this->secNode->childNodes AS $node) {
			if ($node->nodeType == XML_ELEMENT_NODE) {
				$arNodes[] = $node;
			}
		}

		if ($this->signAllHeaders) {
			foreach ($this->secNode->parentNode->childNodes AS $node) {
				if (($node->nodeType == XML_ELEMENT_NODE) &&
				($node->namespaceURI != WSSESoap::WSSENS)) {
					$arNodes[] = $node;
				}
			}
		}

		foreach ($this->envelope->childNodes AS $node) {
			if ($node->namespaceURI == $this->soapNS && $node->localName == 'Body') {
				$arNodes[] = $node;
				break;
			}
		}

		$arOptions = array('prefix'=>WSSESoap::WSUPFX, 'prefix_ns'=>WSSESoap::WSUNS);
		$objDSig->addReferenceList($arNodes, XMLSecurityDSig::SHA1, NULL, $arOptions);

		$objDSig->sign($objKey);

		$objDSig->appendSignature($this->secNode, TRUE);
	}

	public function addEncryptedKey($node, $key, $token) {
		if (! $key->encKey) {
			return FALSE;
		}
		$encKey = $key->encKey;
		$security = $this->locateSecurityHeader();
		$doc = $security->ownerDocument;
		if (! $doc->isSameNode($encKey->ownerDocument)) {
			$key->encKey = $security->ownerDocument->importNode($encKey, TRUE);
			$encKey = $key->encKey;
		}
		if (! empty($key->guid)) {
			return TRUE;
		}

		$lastToken = NULL;
		$findTokens = $security->firstChild;
		while ($findTokens) {
			if ($findTokens->localName == 'BinarySecurityToken') {
				$lastToken = $findTokens;
			}
			$findTokens = $findTokens->nextSibling;
		}
		if ($lastToken) {
			$lastToken = $lastToken->nextSibling;
		}

		$security->insertBefore($encKey, $lastToken);
		$key->guid = XMLSecurityDSig::generate_GUID();
		$encKey->setAttribute('Id', $key->guid);
		$encMethod = $encKey->firstChild;
		while ($encMethod && $encMethod->localName != 'EncryptionMethod') {
			$encMethod = $encMethod->nextChild;
		}
		if ($encMethod) {
			$encMethod = $encMethod->nextSibling;
		}
		$objDoc = $encKey->ownerDocument;
		$keyInfo = $objDoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'dsig:KeyInfo');
		$encKey->insertBefore($keyInfo, $encMethod);
		$tokenURI = '#'.$token->getAttributeNS(WSSESoap::WSUNS, "Id");
		$tokenRef = $objDoc->createElementNS(WSSESoap::WSSENS, WSSESoap::WSSEPFX.':SecurityTokenReference');
		$keyInfo->appendChild($tokenRef);
		$reference = $objDoc->createElementNS(WSSESoap::WSSENS, WSSESoap::WSSEPFX.':Reference');
		$reference->setAttribute("URI", $tokenURI);
		$tokenRef->appendChild($reference);

		return TRUE;
	}

	public function AddReference($baseNode, $guid) {
		$refList = NULL;
		$child = $baseNode->firstChild;
		while($child) {
			if (($child->namespaceURI == XMLSecEnc::XMLENCNS) && ($child->localName == 'ReferenceList')) {
				$refList = $child;
				break;
			}
			$child = $child->nextSibling;
		}
		$doc = $baseNode->ownerDocument;
		if (is_null($refList)) {
			$refList = $doc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:ReferenceList');
			$baseNode->appendChild($refList);
		}
		$dataref = $doc->createElementNS(XMLSecEnc::XMLENCNS, 'xenc:DataReference');
		$refList->appendChild($dataref);
		$dataref->setAttribute('URI', '#'.$guid);
	}

	public function EncryptBody($siteKey, $objKey, $token) {

		$enc = new XMLSecEnc();
		foreach ($this->envelope->childNodes AS $node) {
			if ($node->namespaceURI == $this->soapNS && $node->localName == 'Body') {
				break;
			}
		}
		$enc->setNode($node);
		/* encrypt the symmetric key */
		$enc->encryptKey($siteKey, $objKey, FALSE);

		$enc->type = XMLSecEnc::Content;
		/* Using the symmetric key to actually encrypt the data */
		$encNode = $enc->encryptNode($objKey);

		$guid = XMLSecurityDSig::generate_GUID();
		$encNode->setAttribute('Id', $guid);

		$refNode = $encNode->firstChild;
		while($refNode && $refNode->nodeType != XML_ELEMENT_NODE) {
			$refNode = $refNode->nextSibling;
		}
		if ($refNode) {
			$refNode = $refNode->nextSibling;
		}
		if ($this->addEncryptedKey($encNode, $enc, $token)) {
			$this->AddReference($enc->encKey, $guid);
		}
	}

	public function saveXML() {
		return $this->soapDoc->saveXML();
	}

	public function save($file) {
		return $this->soapDoc->save($file);
	}
}
/*
	End soap-wsse.php
	Start exacttarget_soap_client.php
 **/

class ExactTargetSoapClient extends SoapClient {
	public $username = NULL;
	public $password = NULL;

	function __doRequest($request, $location, $saction, $version, $one_way = 0) {
		$doc = new DOMDocument();
		$doc->loadXML($request);

		$objWSSE = new WSSESoap($doc);

		$objWSSE->addUserToken($this->username, $this->password, FALSE);

		return parent::__doRequest($objWSSE->saveXML(), $location, $saction, $version, $one_way = 0);
   }

}

class ExactTarget_APIFault {
  public $Code; // int
  public $Message; // string
  public $LogID; // long
  public $Params; // ExactTarget_Params
}

class ExactTarget_Params {
  public $Param; // string
}

class ExactTarget_APIObject {
  public $Client; // ExactTarget_ClientID
  public $PartnerKey; // string
  public $PartnerProperties; // ExactTarget_APIProperty
  public $CreatedDate; // dateTime
  public $ModifiedDate; // dateTime
  public $ID; // int
  public $ObjectID; // string
  public $CustomerKey; // string
  public $Owner; // ExactTarget_Owner
  public $CorrelationID; // string
  public $ObjectState; // string
}

class ExactTarget_ClientID {
  public $ClientID; // int
  public $ID; // int
  public $PartnerClientKey; // string
  public $UserID; // int
  public $PartnerUserKey; // string
  public $CreatedBy; // int
  public $ModifiedBy; // int
  public $EnterpriseID; // long
  public $CustomerKey; // string
}

class ExactTarget_APIProperty {
  public $Name; // string
  public $Value; // string
}

class ExactTarget_NullAPIProperty {
}

class ExactTarget_DataFolder {
  public $ParentFolder; // ExactTarget_DataFolder
  public $Name; // string
  public $Description; // string
  public $ContentType; // string
  public $IsActive; // boolean
  public $IsEditable; // boolean
  public $AllowChildren; // boolean
}

class ExactTarget_Owner {
  public $Client; // ExactTarget_ClientID
  public $FromName; // string
  public $FromAddress; // string
  public $User; // ExactTarget_AccountUser
}

class ExactTarget_AsyncResponseType {
  const None='None';
  const email='email';
  const FTP='FTP';
  const HTTPPost='HTTPPost';
}

class ExactTarget_AsyncResponse {
  public $ResponseType; // ExactTarget_AsyncResponseType
  public $ResponseAddress; // string
  public $RespondWhen; // ExactTarget_RespondWhen
  public $IncludeResults; // boolean
  public $IncludeObjects; // boolean
  public $OnlyIncludeBase; // boolean
}

class ExactTarget_ContainerID {
  public $APIObject; // ExactTarget_APIObject
}

class ExactTarget_Request {
}

class ExactTarget_Result {
  public $StatusCode; // string
  public $StatusMessage; // string
  public $OrdinalID; // int
  public $ErrorCode; // int
  public $RequestID; // string
  public $ConversationID; // string
  public $OverallStatusCode; // string
  public $RequestType; // ExactTarget_RequestType
  public $ResultType; // string
  public $ResultDetailXML; // string
}

class ExactTarget_ResultMessage {
  public $RequestID; // string
  public $ConversationID; // string
  public $OverallStatusCode; // string
  public $StatusCode; // string
  public $StatusMessage; // string
  public $ErrorCode; // int
  public $RequestType; // ExactTarget_RequestType
  public $ResultType; // string
  public $ResultDetailXML; // string
  public $SequenceCode; // int
  public $CallsInConversation; // int
}

class ExactTarget_ResultItem {
  public $RequestID; // string
  public $ConversationID; // string
  public $StatusCode; // string
  public $StatusMessage; // string
  public $OrdinalID; // int
  public $ErrorCode; // int
  public $RequestType; // ExactTarget_RequestType
  public $RequestObjectType; // string
}

class ExactTarget_Priority {
  const Low='Low';
  const Medium='Medium';
  const High='High';
}

class ExactTarget_Options {
  public $Client; // ExactTarget_ClientID
  public $SendResponseTo; // ExactTarget_AsyncResponse
  public $SaveOptions; // ExactTarget_SaveOptions
  public $Priority; // byte
  public $ConversationID; // string
  public $SequenceCode; // int
  public $CallsInConversation; // int
  public $ScheduledTime; // dateTime
  public $RequestType; // ExactTarget_RequestType
  public $QueuePriority; // ExactTarget_Priority
}

class ExactTarget_SaveOptions {
  public $SaveOption; // ExactTarget_SaveOption
}

class ExactTarget_TaskResult {
  public $StatusCode; // string
  public $StatusMessage; // string
  public $OrdinalID; // int
  public $ErrorCode; // int
  public $ID; // string
  public $InteractionObjectID; // string
}

class ExactTarget_RequestType {
  const Synchronous='Synchronous';
  const Asynchronous='Asynchronous';
}

class ExactTarget_RespondWhen {
  const Never='Never';
  const OnError='OnError';
  const Always='Always';
  const OnConversationError='OnConversationError';
  const OnConversationComplete='OnConversationComplete';
  const OnCallComplete='OnCallComplete';
}

class ExactTarget_SaveOption {
  public $PropertyName; // string
  public $SaveAction; // ExactTarget_SaveAction
}

class ExactTarget_SaveAction {
  const AddOnly='AddOnly';
  const _Default='Default';
  const Nothing='Nothing';
  const UpdateAdd='UpdateAdd';
  const UpdateOnly='UpdateOnly';
  const Delete='Delete';
}

class ExactTarget_CreateRequest {
  public $Options; // ExactTarget_CreateOptions
  public $Objects; // ExactTarget_APIObject
}

class ExactTarget_CreateResult {
  public $NewID; // int
  public $NewObjectID; // string
  public $PartnerKey; // string
  public $Object; // ExactTarget_APIObject
  public $CreateResults; // ExactTarget_CreateResult
  public $ParentPropertyName; // string
}

class ExactTarget_CreateResponse {
  public $Results; // ExactTarget_CreateResult
  public $RequestID; // string
  public $OverallStatus; // string
}

class ExactTarget_CreateOptions {
  public $Container; // ExactTarget_ContainerID
}

class ExactTarget_UpdateOptions {
  public $Container; // ExactTarget_ContainerID
  public $Action; // string
}

class ExactTarget_UpdateRequest {
  public $Options; // ExactTarget_UpdateOptions
  public $Objects; // ExactTarget_APIObject
}

class ExactTarget_UpdateResult {
  public $Object; // ExactTarget_APIObject
  public $UpdateResults; // ExactTarget_UpdateResult
  public $ParentPropertyName; // string
}

class ExactTarget_UpdateResponse {
  public $Results; // ExactTarget_UpdateResult
  public $RequestID; // string
  public $OverallStatus; // string
}

class ExactTarget_DeleteOptions {
}

class ExactTarget_DeleteRequest {
  public $Options; // ExactTarget_DeleteOptions
  public $Objects; // ExactTarget_APIObject
}

class ExactTarget_DeleteResult {
  public $Object; // ExactTarget_APIObject
}

class ExactTarget_DeleteResponse {
  public $Results; // ExactTarget_DeleteResult
  public $RequestID; // string
  public $OverallStatus; // string
}

class ExactTarget_RetrieveRequest {
  public $ClientIDs; // ExactTarget_ClientID
  public $ObjectType; // string
  public $Properties; // string
  public $Filter; // ExactTarget_FilterPart
  public $RespondTo; // ExactTarget_AsyncResponse
  public $PartnerProperties; // ExactTarget_APIProperty
  public $ContinueRequest; // string
  public $QueryAllAccounts; // boolean
  public $RetrieveAllSinceLastBatch; // boolean
  public $RepeatLastResult; // boolean
  public $Retrieves; // ExactTarget_Retrieves
  public $Options; // ExactTarget_RetrieveOptions
}

class ExactTarget_Retrieves {
  public $Request; // ExactTarget_Request
}

class ExactTarget_RetrieveRequestMsg {
  public $RetrieveRequest; // ExactTarget_RetrieveRequest
}

class ExactTarget_RetrieveResponseMsg {
  public $OverallStatus; // string
  public $RequestID; // string
  public $Results; // ExactTarget_APIObject
}

class ExactTarget_RetrieveSingleRequest {
  public $RequestedObject; // ExactTarget_APIObject
  public $RetrieveOption; // ExactTarget_Options
}

class ExactTarget_Parameters {
  public $Parameter; // ExactTarget_APIProperty
}

class ExactTarget_RetrieveSingleOptions {
  public $Parameters; // ExactTarget_Parameters
}

class ExactTarget_RetrieveOptions {
  public $BatchSize; // int
  public $IncludeObjects; // boolean
  public $OnlyIncludeBase; // boolean
}

class ExactTarget_QueryRequest {
  public $ClientIDs; // ExactTarget_ClientID
  public $Query; // ExactTarget_Query
  public $RespondTo; // ExactTarget_AsyncResponse
  public $PartnerProperties; // ExactTarget_APIProperty
  public $ContinueRequest; // string
  public $QueryAllAccounts; // boolean
  public $RetrieveAllSinceLastBatch; // boolean
}

class ExactTarget_QueryRequestMsg {
  public $QueryRequest; // ExactTarget_QueryRequest
}

class ExactTarget_QueryResponseMsg {
  public $OverallStatus; // string
  public $RequestID; // string
  public $Results; // ExactTarget_APIObject
}

class ExactTarget_QueryObject {
  public $ObjectType; // string
  public $Properties; // string
  public $Objects; // ExactTarget_QueryObject
}

class ExactTarget_Query {
  public $Object; // ExactTarget_QueryObject
  public $Filter; // ExactTarget_FilterPart
}

class ExactTarget_FilterPart {
}

class ExactTarget_SimpleFilterPart {
  public $Property; // string
  public $SimpleOperator; // ExactTarget_SimpleOperators
  public $Value; // string
  public $DateValue; // dateTime
}

class ExactTarget_TagFilterPart {
  public $Tags; // ExactTarget_Tags
}

class ExactTarget_Tags {
  public $Tag; // string
}

class ExactTarget_ComplexFilterPart {
  public $LeftOperand; // ExactTarget_FilterPart
  public $LogicalOperator; // ExactTarget_LogicalOperators
  public $RightOperand; // ExactTarget_FilterPart
  public $AdditionalOperands; // ExactTarget_AdditionalOperands
}

class ExactTarget_AdditionalOperands {
  public $Operand; // ExactTarget_FilterPart
}

class ExactTarget_SimpleOperators {
  const equals='equals';
  const notEquals='notEquals';
  const greaterThan='greaterThan';
  const lessThan='lessThan';
  const isNull='isNull';
  const isNotNull='isNotNull';
  const greaterThanOrEqual='greaterThanOrEqual';
  const lessThanOrEqual='lessThanOrEqual';
  const between='between';
  const IN='IN';
  const like='like';
  const existsInString='existsInString';
  const existsInStringAsAWord='existsInStringAsAWord';
  const notExistsInString='notExistsInString';
  const beginsWith='beginsWith';
  const endsWith='endsWith';
  const contains='contains';
  const notContains='notContains';
  const isAnniversary='isAnniversary';
  const isNotAnniversary='isNotAnniversary';
  const greaterThanAnniversary='greaterThanAnniversary';
  const lessThanAnniversary='lessThanAnniversary';
}

class ExactTarget_LogicalOperators {
  const _OR='OR';
  const _AND='AND';
}

class ExactTarget_DefinitionRequestMsg {
  public $DescribeRequests; // ExactTarget_ArrayOfObjectDefinitionRequest
}

class ExactTarget_ArrayOfObjectDefinitionRequest {
  public $ObjectDefinitionRequest; // ExactTarget_ObjectDefinitionRequest
}

class ExactTarget_ObjectDefinitionRequest {
  public $Client; // ExactTarget_ClientID
  public $ObjectType; // string
}

class ExactTarget_DefinitionResponseMsg {
  public $ObjectDefinition; // ExactTarget_ObjectDefinition
  public $RequestID; // string
}

class ExactTarget_PropertyDefinition {
  public $Name; // string
  public $DataType; // string
  public $ValueType; // ExactTarget_SoapType
  public $PropertyType; // ExactTarget_PropertyType
  public $IsCreatable; // boolean
  public $IsUpdatable; // boolean
  public $IsRetrievable; // boolean
  public $IsQueryable; // boolean
  public $IsFilterable; // boolean
  public $IsPartnerProperty; // boolean
  public $IsAccountProperty; // boolean
  public $PartnerMap; // string
  public $AttributeMaps; // ExactTarget_AttributeMap
  public $Markups; // ExactTarget_APIProperty
  public $Precision; // int
  public $Scale; // int
  public $Label; // string
  public $Description; // string
  public $DefaultValue; // string
  public $MinLength; // int
  public $MaxLength; // int
  public $MinValue; // string
  public $MaxValue; // string
  public $IsRequired; // boolean
  public $IsViewable; // boolean
  public $IsEditable; // boolean
  public $IsNillable; // boolean
  public $IsRestrictedPicklist; // boolean
  public $PicklistItems; // ExactTarget_PicklistItems
  public $IsSendTime; // boolean
  public $DisplayOrder; // int
  public $References; // ExactTarget_References
  public $RelationshipName; // string
  public $Status; // string
  public $IsContextSpecific; // boolean
}

class ExactTarget_PicklistItems {
  public $PicklistItem; // ExactTarget_PicklistItem
}

class ExactTarget_References {
  public $Reference; // ExactTarget_APIObject
}

class ExactTarget_ObjectDefinition {
  public $ObjectType; // string
  public $Name; // string
  public $IsCreatable; // boolean
  public $IsUpdatable; // boolean
  public $IsRetrievable; // boolean
  public $IsQueryable; // boolean
  public $IsReference; // boolean
  public $ReferencedType; // string
  public $IsPropertyCollection; // string
  public $IsObjectCollection; // boolean
  public $Properties; // ExactTarget_PropertyDefinition
  public $ExtendedProperties; // ExactTarget_ExtendedProperties
  public $ChildObjects; // ExactTarget_ObjectDefinition
}

class ExactTarget_ExtendedProperties {
  public $ExtendedProperty; // ExactTarget_PropertyDefinition
}

class ExactTarget_AttributeMap {
  public $EntityName; // string
  public $ColumnName; // string
  public $ColumnNameMappedTo; // string
  public $EntityNameMappedTo; // string
  public $AdditionalData; // ExactTarget_APIProperty
}

class ExactTarget_PicklistItem {
  public $IsDefaultValue; // boolean
  public $Label; // string
  public $Value; // string
}

class ExactTarget_SoapType {
  const xsd_string='xsd:string';
  const xsd_boolean='xsd:boolean';
  const xsd_double='xsd:double';
  const xsd_dateTime='xsd:dateTime';
}

class ExactTarget_PropertyType {
  const string='string';
  const boolean='boolean';
  const double='double';
  const datetime='datetime';
}

class ExactTarget_ExecuteRequest {
  public $Client; // ExactTarget_ClientID
  public $Name; // string
  public $Parameters; // ExactTarget_APIProperty
}

class ExactTarget_ExecuteResponse {
  public $StatusCode; // string
  public $StatusMessage; // string
  public $OrdinalID; // int
  public $Results; // ExactTarget_APIProperty
  public $ErrorCode; // int
}

class ExactTarget_ExecuteRequestMsg {
  public $Requests; // ExactTarget_ExecuteRequest
}

class ExactTarget_ExecuteResponseMsg {
  public $OverallStatus; // string
  public $RequestID; // string
  public $Results; // ExactTarget_ExecuteResponse
}

class ExactTarget_InteractionDefinition {
  public $InteractionObjectID; // string
}

class ExactTarget_InteractionBaseObject {
  public $Name; // string
  public $Description; // string
  public $Keyword; // string
}

class ExactTarget_PerformOptions {
  public $Explanation; // string
}

class ExactTarget_CampaignPerformOptions {
  public $OccurrenceIDs; // string
  public $OccurrenceIDsIndex; // int
}

class ExactTarget_PerformRequest {
  public $Client; // ExactTarget_ClientID
  public $Action; // string
  public $Definitions; // ExactTarget_Definitions
}

class ExactTarget_Definitions {
  public $Definition; // ExactTarget_InteractionBaseObject
}

class ExactTarget_PerformResponse {
  public $StatusCode; // string
  public $StatusMessage; // string
  public $OrdinalID; // int
  public $Results; // ExactTarget_Results
  public $ErrorCode; // int
}

class ExactTarget_Results {
  public $Result; // ExactTarget_APIProperty
}

class ExactTarget_PerformResult {
  public $Object; // ExactTarget_APIObject
  public $Task; // ExactTarget_TaskResult
}

class ExactTarget_PerformRequestMsg {
  public $Options; // ExactTarget_PerformOptions
  public $Action; // string
  public $Definitions; // ExactTarget_Definitions
}


class ExactTarget_PerformResponseMsg {
  public $Results; // ExactTarget_Results
  public $OverallStatus; // string
  public $OverallStatusMessage; // string
  public $RequestID; // string
}


class ExactTarget_ValidationAction {
  public $ValidationType; // string
  public $ValidationOptions; // ExactTarget_ValidationOptions
}

class ExactTarget_ValidationOptions {
  public $ValidationOption; // ExactTarget_APIProperty
}

class ExactTarget_SpamAssassinValidation {
}

class ExactTarget_ContentValidation {
  public $ValidationAction; // ExactTarget_ValidationAction
  public $Email; // ExactTarget_Email
  public $Subscribers; // ExactTarget_Subscribers
}

class ExactTarget_Subscribers {
  public $Subscriber; // ExactTarget_Subscriber
}

class ExactTarget_ContentValidationResult {
}

class ExactTarget_ValidationResult {
  public $Subscriber; // ExactTarget_Subscriber
  public $CheckTime; // dateTime
  public $CheckTimeUTC; // dateTime
  public $IsResultValid; // boolean
  public $IsSpam; // boolean
  public $Score; // double
  public $Threshold; // double
  public $Message; // string
}

class ExactTarget_ContentValidationTaskResult {
  public $ValidationResults; // ExactTarget_ValidationResults
}

class ExactTarget_ValidationResults {
  public $ValidationResult; // ExactTarget_ValidationResult
}

class ExactTarget_ConfigureOptions {
}

class ExactTarget_ConfigureResult {
  public $Object; // ExactTarget_APIObject
}

class ExactTarget_ConfigureRequestMsg {
  public $Options; // ExactTarget_ConfigureOptions
  public $Action; // string
  public $Configurations; // ExactTarget_Configurations
}

class ExactTarget_Configurations {
  public $Configuration; // ExactTarget_APIObject
}

class ExactTarget_ConfigureResponseMsg {
  public $Results; // ExactTarget_Results
  public $OverallStatus; // string
  public $OverallStatusMessage; // string
  public $RequestID; // string
}


class ExactTarget_ScheduleDefinition {
  public $Name; // string
  public $Description; // string
  public $Recurrence; // ExactTarget_Recurrence
  public $RecurrenceType; // ExactTarget_RecurrenceTypeEnum
  public $RecurrenceRangeType; // ExactTarget_RecurrenceRangeTypeEnum
  public $StartDateTime; // dateTime
  public $EndDateTime; // dateTime
  public $Occurrences; // int
  public $Keyword; // string
  public $TimeZone; // ExactTarget_TimeZone
}

class ExactTarget_ScheduleOptions {
}

class ExactTarget_ScheduleResponse {
  public $StatusCode; // string
  public $StatusMessage; // string
  public $OrdinalID; // int
  public $Results; // ExactTarget_Results
  public $ErrorCode; // int
}


class ExactTarget_ScheduleResult {
  public $Object; // ExactTarget_ScheduleDefinition
  public $Task; // ExactTarget_TaskResult
}

class ExactTarget_ScheduleRequestMsg {
  public $Options; // ExactTarget_ScheduleOptions
  public $Action; // string
  public $Schedule; // ExactTarget_ScheduleDefinition
  public $Interactions; // ExactTarget_Interactions
}

class ExactTarget_Interactions {
  public $Interaction; // ExactTarget_APIObject
}

class ExactTarget_ScheduleResponseMsg {
  public $Results; // ExactTarget_Results
  public $OverallStatus; // string
  public $OverallStatusMessage; // string
  public $RequestID; // string
}


class ExactTarget_RecurrenceTypeEnum {
  const Secondly='Secondly';
  const Minutely='Minutely';
  const Hourly='Hourly';
  const Daily='Daily';
  const Weekly='Weekly';
  const Monthly='Monthly';
  const Yearly='Yearly';
}

class ExactTarget_RecurrenceRangeTypeEnum {
  const EndAfter='EndAfter';
  const EndOn='EndOn';
}

class ExactTarget_Recurrence {
}

class ExactTarget_MinutelyRecurrencePatternTypeEnum {
  const Interval='Interval';
}

class ExactTarget_HourlyRecurrencePatternTypeEnum {
  const Interval='Interval';
}

class ExactTarget_DailyRecurrencePatternTypeEnum {
  const Interval='Interval';
  const EveryWeekDay='EveryWeekDay';
}

class ExactTarget_WeeklyRecurrencePatternTypeEnum {
  const ByDay='ByDay';
}

class ExactTarget_MonthlyRecurrencePatternTypeEnum {
  const ByDay='ByDay';
  const ByWeek='ByWeek';
}

class ExactTarget_WeekOfMonthEnum {
  const first='first';
  const second='second';
  const third='third';
  const fourth='fourth';
  const last='last';
}

class ExactTarget_DayOfWeekEnum {
  const Sunday='Sunday';
  const Monday='Monday';
  const Tuesday='Tuesday';
  const Wednesday='Wednesday';
  const Thursday='Thursday';
  const Friday='Friday';
  const Saturday='Saturday';
}

class ExactTarget_YearlyRecurrencePatternTypeEnum {
  const ByDay='ByDay';
  const ByWeek='ByWeek';
  const ByMonth='ByMonth';
}

class ExactTarget_MonthOfYearEnum {
  const January='January';
  const February='February';
  const March='March';
  const April='April';
  const May='May';
  const June='June';
  const July='July';
  const August='August';
  const September='September';
  const October='October';
  const November='November';
  const December='December';
}

class ExactTarget_MinutelyRecurrence {
  public $MinutelyRecurrencePatternType; // ExactTarget_MinutelyRecurrencePatternTypeEnum
  public $MinuteInterval; // int
}

class ExactTarget_HourlyRecurrence {
  public $HourlyRecurrencePatternType; // ExactTarget_HourlyRecurrencePatternTypeEnum
  public $HourInterval; // int
}

class ExactTarget_DailyRecurrence {
  public $DailyRecurrencePatternType; // ExactTarget_DailyRecurrencePatternTypeEnum
  public $DayInterval; // int
}

class ExactTarget_WeeklyRecurrence {
  public $WeeklyRecurrencePatternType; // ExactTarget_WeeklyRecurrencePatternTypeEnum
  public $WeekInterval; // int
  public $Sunday; // boolean
  public $Monday; // boolean
  public $Tuesday; // boolean
  public $Wednesday; // boolean
  public $Thursday; // boolean
  public $Friday; // boolean
  public $Saturday; // boolean
}

class ExactTarget_MonthlyRecurrence {
  public $MonthlyRecurrencePatternType; // ExactTarget_MonthlyRecurrencePatternTypeEnum
  public $MonthlyInterval; // int
  public $ScheduledDay; // int
  public $ScheduledWeek; // ExactTarget_WeekOfMonthEnum
  public $ScheduledDayOfWeek; // ExactTarget_DayOfWeekEnum
}

class ExactTarget_YearlyRecurrence {
  public $YearlyRecurrencePatternType; // ExactTarget_YearlyRecurrencePatternTypeEnum
  public $ScheduledDay; // int
  public $ScheduledWeek; // ExactTarget_WeekOfMonthEnum
  public $ScheduledMonth; // ExactTarget_MonthOfYearEnum
  public $ScheduledDayOfWeek; // ExactTarget_DayOfWeekEnum
}

class ExactTarget_ExtractRequest {
  public $Client; // ExactTarget_ClientID
  public $ID; // string
  public $Options; // ExactTarget_ExtractOptions
  public $Parameters; // ExactTarget_Parameters
  public $Description; // ExactTarget_ExtractDescription
  public $Definition; // ExactTarget_ExtractDefinition
}


class ExactTarget_ExtractResult {
  public $Request; // ExactTarget_ExtractRequest
}

class ExactTarget_ExtractRequestMsg {
  public $Requests; // ExactTarget_ExtractRequest
}

class ExactTarget_ExtractResponseMsg {
  public $OverallStatus; // string
  public $RequestID; // string
  public $Results; // ExactTarget_ExtractResult
}

class ExactTarget_ExtractOptions {
}

class ExactTarget_ExtractParameter {
}

class ExactTarget_ExtractTemplate {
  public $Name; // string
  public $ConfigurationPage; // string
  public $PackageKey; // string
}

class ExactTarget_ExtractDescription {
  public $Parameters; // ExactTarget_Parameters
}


class ExactTarget_ExtractDefinition {
  public $Parameters; // ExactTarget_Parameters
  public $Values; // ExactTarget_Values
}


class ExactTarget_Values {
  public $Value; // ExactTarget_APIProperty
}

class ExactTarget_ExtractParameterDataType {
  const datetime='datetime';
  const bool='bool';
  const string='string';
  const integer='integer';
  const dropdown='dropdown';
}

class ExactTarget_ParameterDescription {
}

class ExactTarget_ExtractParameterDescription {
  public $Name; // string
  public $DataType; // ExactTarget_ExtractParameterDataType
  public $DefaultValue; // string
  public $IsOptional; // boolean
  public $DropDownList; // string
}

class ExactTarget_VersionInfoResponse {
  public $Version; // string
  public $VersionDate; // dateTime
  public $Notes; // string
  public $VersionHistory; // ExactTarget_VersionInfoResponse
}

class ExactTarget_VersionInfoRequestMsg {
  public $IncludeVersionHistory; // boolean
}

class ExactTarget_VersionInfoResponseMsg {
  public $VersionInfo; // ExactTarget_VersionInfoResponse
  public $RequestID; // string
}

class ExactTarget_Locale {
  public $LocaleCode; // string
}

class ExactTarget_TimeZone {
  public $Name; // string
}

class ExactTarget_Account {
  public $AccountType; // ExactTarget_AccountTypeEnum
  public $ParentID; // int
  public $BrandID; // int
  public $PrivateLabelID; // int
  public $ReportingParentID; // int
  public $Name; // string
  public $Email; // string
  public $FromName; // string
  public $BusinessName; // string
  public $Phone; // string
  public $Address; // string
  public $Fax; // string
  public $City; // string
  public $State; // string
  public $Zip; // string
  public $Country; // string
  public $IsActive; // int
  public $IsTestAccount; // boolean
  public $OrgID; // int
  public $DBID; // int
  public $ParentName; // string
  public $CustomerID; // long
  public $DeletedDate; // dateTime
  public $EditionID; // int
  public $Children; // ExactTarget_AccountDataItem
  public $Subscription; // ExactTarget_Subscription
  public $PrivateLabels; // ExactTarget_PrivateLabel
  public $BusinessRules; // ExactTarget_BusinessRule
  public $AccountUsers; // ExactTarget_AccountUser
  public $InheritAddress; // boolean
  public $IsTrialAccount; // boolean
  public $Locale; // ExactTarget_Locale
  public $ParentAccount; // ExactTarget_Account
  public $TimeZone; // ExactTarget_TimeZone
  public $Roles; // ExactTarget_Roles
  public $LanguageLocale; // ExactTarget_Locale
}

class ExactTarget_Roles {
  public $Role; // ExactTarget_Role
}

class ExactTarget_BusinessUnit {
  public $Description; // string
  public $DefaultSendClassification; // ExactTarget_SendClassification
  public $DefaultHomePage; // ExactTarget_LandingPage
  public $SubscriberFilter; // ExactTarget_FilterPart
  public $MasterUnsubscribeBehavior; // ExactTarget_UnsubscribeBehaviorEnum
}

class ExactTarget_UnsubscribeBehaviorEnum {
  const ENTIRE_ENTERPRISE='ENTIRE_ENTERPRISE';
  const BUSINESS_UNIT_ONLY='BUSINESS_UNIT_ONLY';
}

class ExactTarget_LandingPage {
}

class ExactTarget_AccountTypeEnum {
  const None='None';
  const EXACTTARGET='EXACTTARGET';
  const PRO_CONNECT='PRO_CONNECT';
  const CHANNEL_CONNECT='CHANNEL_CONNECT';
  const CONNECT='CONNECT';
  const PRO_CONNECT_CLIENT='PRO_CONNECT_CLIENT';
  const LP_MEMBER='LP_MEMBER';
  const DOTO_MEMBER='DOTO_MEMBER';
  const ENTERPRISE_2='ENTERPRISE_2';
  const BUSINESS_UNIT='BUSINESS_UNIT';
}

class ExactTarget_AccountDataItem {
  public $ChildAccountID; // int
  public $BrandID; // int
  public $PrivateLabelID; // int
  public $AccountType; // int
}

class ExactTarget_Subscription {
  public $SubscriptionID; // int
  public $EmailsPurchased; // int
  public $AccountsPurchased; // int
  public $AdvAccountsPurchased; // int
  public $LPAccountsPurchased; // int
  public $DOTOAccountsPurchased; // int
  public $BUAccountsPurchased; // int
  public $BeginDate; // dateTime
  public $EndDate; // dateTime
  public $Notes; // string
  public $Period; // string
  public $NotificationTitle; // string
  public $NotificationMessage; // string
  public $NotificationFlag; // string
  public $NotificationExpDate; // dateTime
  public $ForAccounting; // string
  public $HasPurchasedEmails; // boolean
  public $ContractNumber; // string
  public $ContractModifier; // string
  public $IsRenewal; // boolean
  public $NumberofEmails; // long
}

class ExactTarget_PrivateLabel {
  public $ID; // int
  public $Name; // string
  public $ColorPaletteXML; // string
  public $LogoFile; // string
  public $Delete; // int
  public $SetActive; // boolean
}

class ExactTarget_AccountPrivateLabel {
  public $Name; // string
  public $OwnerMemberID; // int
  public $ColorPaletteXML; // string
}

class ExactTarget_BusinessRule {
  public $MemberBusinessRuleID; // int
  public $BusinessRuleID; // int
  public $Data; // int
  public $Quality; // string
  public $Name; // string
  public $Type; // string
  public $Description; // string
  public $IsViewable; // boolean
  public $IsInheritedFromParent; // boolean
  public $DisplayName; // string
  public $ProductCode; // string
}

class ExactTarget_AccountUser {
  public $AccountUserID; // int
  public $UserID; // string
  public $Password; // string
  public $Name; // string
  public $Email; // string
  public $MustChangePassword; // boolean
  public $ActiveFlag; // boolean
  public $ChallengePhrase; // string
  public $ChallengeAnswer; // string
  public $UserPermissions; // ExactTarget_UserAccess
  public $Delete; // int
  public $LastSuccessfulLogin; // dateTime
  public $IsAPIUser; // boolean
  public $NotificationEmailAddress; // string
  public $IsLocked; // boolean
  public $Unlock; // boolean
  public $BusinessUnit; // int
  public $DefaultBusinessUnit; // int
  public $DefaultApplication; // string
  public $Locale; // ExactTarget_Locale
  public $TimeZone; // ExactTarget_TimeZone
  public $DefaultBusinessUnitObject; // ExactTarget_BusinessUnit
  public $AssociatedBusinessUnits; // ExactTarget_AssociatedBusinessUnits
  public $Roles; // ExactTarget_Roles
  public $LanguageLocale; // ExactTarget_Locale
  public $SsoIdentities; // ExactTarget_SsoIdentities
}

class ExactTarget_AssociatedBusinessUnits {
  public $BusinessUnit; // ExactTarget_BusinessUnit
}


class ExactTarget_SsoIdentities {
  public $SsoIdentity; // ExactTarget_SsoIdentity
}

class ExactTarget_SsoIdentity {
  public $FederatedID; // string
  public $IsActive; // boolean
}

class ExactTarget_UserAccess {
  public $Name; // string
  public $Value; // string
  public $Description; // string
  public $Delete; // int
}

class ExactTarget_Brand {
  public $BrandID; // int
  public $Label; // string
  public $Comment; // string
  public $BrandTags; // ExactTarget_BrandTag
}

class ExactTarget_BrandTag {
  public $BrandID; // int
  public $Label; // string
  public $Data; // string
}

class ExactTarget_Role {
  public $Name; // string
  public $Description; // string
  public $IsPrivate; // boolean
  public $IsSystemDefined; // boolean
  public $ForceInheritance; // boolean
  public $PermissionSets; // ExactTarget_PermissionSets
  public $Permissions; // ExactTarget_Permissions
}

class ExactTarget_PermissionSets {
  public $PermissionSet; // ExactTarget_PermissionSet
}

class ExactTarget_Permissions {
  public $Permission; // ExactTarget_Permission
}

class ExactTarget_PermissionSet {
  public $Name; // string
  public $Description; // string
  public $IsAllowed; // boolean
  public $IsDenied; // boolean
  public $PermissionSets; // ExactTarget_PermissionSets
  public $Permissions; // ExactTarget_Permissions
}



class ExactTarget_Permission {
  public $Name; // string
  public $Description; // string
  public $ObjectType; // string
  public $Operation; // string
  public $IsShareable; // boolean
  public $IsAllowed; // boolean
  public $IsDenied; // boolean
}

class ExactTarget_Email {
  public $Name; // string
  public $Folder; // string
  public $CategoryID; // int
  public $HTMLBody; // string
  public $TextBody; // string
  public $ContentAreas; // ExactTarget_ContentArea
  public $Subject; // string
  public $IsActive; // boolean
  public $IsHTMLPaste; // boolean
  public $ClonedFromID; // int
  public $Status; // string
  public $EmailType; // string
  public $CharacterSet; // string
  public $HasDynamicSubjectLine; // boolean
  public $ContentCheckStatus; // string
  public $SyncTextWithHTML; // boolean
  public $PreHeader; // string
  public $IsApproved; // boolean
}

class ExactTarget_ContentArea {
  public $Key; // string
  public $Content; // string
  public $IsBlank; // boolean
  public $CategoryID; // int
  public $Name; // string
  public $Layout; // ExactTarget_LayoutType
  public $IsDynamicContent; // boolean
  public $IsSurvey; // boolean
  public $BackgroundColor; // string
  public $BorderColor; // string
  public $BorderWidth; // int
  public $Cellpadding; // int
  public $Cellspacing; // int
  public $Width; // string
  public $FontFamily; // string
  public $HasFontSize; // boolean
  public $IsLocked; // boolean
}

class ExactTarget_LayoutType {
  const HTMLWrapped='HTMLWrapped';
  const RawText='RawText';
  const SMS='SMS';
}

class ExactTarget_Message {
  public $TextBody; // string
}

class ExactTarget_TrackingEvent {
  public $SendID; // int
  public $SubscriberKey; // string
  public $EventDate; // dateTime
  public $EventType; // ExactTarget_EventType
  public $TriggeredSendDefinitionObjectID; // string
  public $BatchID; // int
}

class ExactTarget_EventType {
  const Open='Open';
  const Click='Click';
  const HardBounce='HardBounce';
  const SoftBounce='SoftBounce';
  const OtherBounce='OtherBounce';
  const Unsubscribe='Unsubscribe';
  const Sent='Sent';
  const NotSent='NotSent';
  const Survey='Survey';
  const ForwardedEmail='ForwardedEmail';
  const ForwardedEmailOptIn='ForwardedEmailOptIn';
  const DeliveredEvent='DeliveredEvent';
}

class ExactTarget_OpenEvent {
}

class ExactTarget_BounceEvent {
  public $SMTPCode; // string
  public $BounceCategory; // string
  public $SMTPReason; // string
  public $BounceType; // string
}

class ExactTarget_UnsubEvent {
  public $List; // ExactTarget_List
  public $IsMasterUnsubscribed; // boolean
}

class ExactTarget_ClickEvent {
  public $URLID; // int
  public $URL; // string
}

class ExactTarget_SentEvent {
}

class ExactTarget_NotSentEvent {
}

class ExactTarget_SurveyEvent {
  public $Question; // string
  public $Answer; // string
}

class ExactTarget_ForwardedEmailEvent {
}

class ExactTarget_ForwardedEmailOptInEvent {
  public $OptInSubscriberKey; // string
}

class ExactTarget_DeliveredEvent {
}

class ExactTarget_Subscriber {
  public $EmailAddress; // string
  public $Attributes; // ExactTarget_Attribute
  public $SubscriberKey; // string
  public $UnsubscribedDate; // dateTime
  public $Status; // ExactTarget_SubscriberStatus
  public $PartnerType; // string
  public $EmailTypePreference; // ExactTarget_EmailType
  public $Lists; // ExactTarget_SubscriberList
  public $GlobalUnsubscribeCategory; // ExactTarget_GlobalUnsubscribeCategory
  public $SubscriberTypeDefinition; // ExactTarget_SubscriberTypeDefinition
  public $Addresses; // ExactTarget_Addresses
  public $PrimarySMSAddress; // ExactTarget_SMSAddress
  public $PrimarySMSPublicationStatus; // ExactTarget_SubscriberAddressStatus
  public $PrimaryEmailAddress; // ExactTarget_EmailAddress
  public $Locale; // ExactTarget_Locale
}

class ExactTarget_Addresses {
  public $Address; // ExactTarget_SubscriberAddress
}

class ExactTarget_Attribute {
  public $Name; // string
  public $Value; // string
  public $Compression; // ExactTarget_CompressionConfiguration
}

class ExactTarget_CompressionConfiguration {
  public $Type; // ExactTarget_CompressionType
  public $Encoding; // ExactTarget_CompressionEncoding
}

class ExactTarget_CompressionType {
  const gzip='gzip';
}

class ExactTarget_CompressionEncoding {
  const base64='base64';
}

class ExactTarget_SubscriberStatus {
  const Active='Active';
  const Bounced='Bounced';
  const Held='Held';
  const Unsubscribed='Unsubscribed';
  const Deleted='Deleted';
}

class ExactTarget_SubscriberTypeDefinition {
  public $SubscriberType; // string
}

class ExactTarget_EmailType {
  const Text='Text';
  const HTML='HTML';
}

class ExactTarget_ListSubscriber {
  public $Status; // ExactTarget_SubscriberStatus
  public $ListID; // int
  public $SubscriberKey; // string
}

class ExactTarget_SubscriberList {
  public $Status; // ExactTarget_SubscriberStatus
  public $List; // ExactTarget_List
  public $Action; // string
  public $Subscriber; // ExactTarget_Subscriber
}

class ExactTarget_List {
  public $ListName; // string
  public $Category; // int
  public $Type; // ExactTarget_ListTypeEnum
  public $Description; // string
  public $Subscribers; // ExactTarget_Subscriber
  public $ListClassification; // ExactTarget_ListClassificationEnum
  public $AutomatedEmail; // ExactTarget_Email
  public $SendClassification; // ExactTarget_SendClassification
}

class ExactTarget_ListTypeEnum {
  const _Public='Public';
  const _Private='Private';
  const SalesForce='SalesForce';
  const GlobalUnsubscribe='GlobalUnsubscribe';
  const Master='Master';
}

class ExactTarget_ListClassificationEnum {
  const ExactTargetList='ExactTargetList';
  const PublicationList='PublicationList';
  const SuppressionList='SuppressionList';
}

class ExactTarget_Group {
  public $Name; // string
  public $Category; // int
  public $Description; // string
  public $Subscribers; // ExactTarget_Subscriber
}

class ExactTarget_OverrideType {
  const DoNotOverride='DoNotOverride';
  const Override='Override';
  const OverrideExceptWhenNull='OverrideExceptWhenNull';
}

class ExactTarget_ListAttributeFieldType {
  const Text='Text';
  const Number='Number';
  const Date='Date';
  const Boolean='Boolean';
  const Decimal='Decimal';
}

class ExactTarget_ListAttribute {
  public $List; // ExactTarget_List
  public $Name; // string
  public $Description; // string
  public $FieldType; // ExactTarget_ListAttributeFieldType
  public $FieldLength; // int
  public $Scale; // int
  public $MinValue; // string
  public $MaxValue; // string
  public $DefaultValue; // string
  public $IsNullable; // boolean
  public $IsHidden; // boolean
  public $IsReadOnly; // boolean
  public $Inheritable; // boolean
  public $Overridable; // boolean
  public $MustOverride; // boolean
  public $OverrideType; // ExactTarget_OverrideType
  public $Ordinal; // int
  public $RestrictedValues; // ExactTarget_ListAttributeRestrictedValue
  public $BaseAttribute; // ExactTarget_ListAttribute
}

class ExactTarget_ListAttributeRestrictedValue {
  public $ValueName; // string
  public $IsDefault; // boolean
  public $DisplayOrder; // int
  public $Description; // string
}

class ExactTarget_GlobalUnsubscribeCategory {
  public $Name; // string
  public $IgnorableByPartners; // boolean
  public $Ignore; // boolean
}

class ExactTarget_Campaign {
}

class ExactTarget_Send {
  public $Email; // ExactTarget_Email
  public $List; // ExactTarget_List
  public $SendDate; // dateTime
  public $FromAddress; // string
  public $FromName; // string
  public $Duplicates; // int
  public $InvalidAddresses; // int
  public $ExistingUndeliverables; // int
  public $ExistingUnsubscribes; // int
  public $HardBounces; // int
  public $SoftBounces; // int
  public $OtherBounces; // int
  public $ForwardedEmails; // int
  public $UniqueClicks; // int
  public $UniqueOpens; // int
  public $NumberSent; // int
  public $NumberDelivered; // int
  public $Unsubscribes; // int
  public $MissingAddresses; // int
  public $Subject; // string
  public $PreviewURL; // string
  public $Links; // ExactTarget_Link
  public $Events; // ExactTarget_TrackingEvent
  public $SentDate; // dateTime
  public $EmailName; // string
  public $Status; // string
  public $IsMultipart; // boolean
  public $SendLimit; // int
  public $SendWindowOpen; // time
  public $SendWindowClose; // time
  public $IsAlwaysOn; // boolean
  public $Sources; // ExactTarget_Sources
  public $NumberTargeted; // int
  public $NumberErrored; // int
  public $NumberExcluded; // int
  public $Additional; // string
  public $BccEmail; // string
  public $EmailSendDefinition; // ExactTarget_EmailSendDefinition
  public $SuppressionLists; // ExactTarget_SuppressionLists
}

class ExactTarget_Sources {
  public $Source; // ExactTarget_APIObject
}

class ExactTarget_SuppressionLists {
  public $SuppressionList; // ExactTarget_AudienceItem
}

class ExactTarget_Link {
  public $LastClicked; // dateTime
  public $Alias; // string
  public $TotalClicks; // int
  public $UniqueClicks; // int
  public $URL; // string
  public $Subscribers; // ExactTarget_TrackingEvent
}

class ExactTarget_SendSummary {
  public $AccountID; // int
  public $AccountName; // string
  public $AccountEmail; // string
  public $IsTestAccount; // boolean
  public $SendID; // int
  public $DeliveredTime; // string
  public $TotalSent; // int
  public $Transactional; // int
  public $NonTransactional; // int
}

class ExactTarget_TriggeredSendDefinition {
  public $TriggeredSendType; // ExactTarget_TriggeredSendTypeEnum
  public $TriggeredSendStatus; // ExactTarget_TriggeredSendStatusEnum
  public $Email; // ExactTarget_Email
  public $List; // ExactTarget_List
  public $AutoAddSubscribers; // boolean
  public $AutoUpdateSubscribers; // boolean
  public $BatchInterval; // int
  public $BccEmail; // string
  public $EmailSubject; // string
  public $DynamicEmailSubject; // string
  public $IsMultipart; // boolean
  public $IsWrapped; // boolean
  public $AllowedSlots; // short
  public $NewSlotTrigger; // int
  public $SendLimit; // int
  public $SendWindowOpen; // time
  public $SendWindowClose; // time
  public $SendWindowDelete; // boolean
  public $RefreshContent; // boolean
  public $ExclusionFilter; // string
  public $Priority; // string
  public $SendSourceCustomerKey; // string
  public $ExclusionListCollection; // ExactTarget_TriggeredSendExclusionList
  public $CCEmail; // string
  public $SendSourceDataExtension; // ExactTarget_DataExtension
  public $IsAlwaysOn; // boolean
  public $DisableOnEmailBuildError; // boolean
  public $PreHeader; // string
  public $ReplyToAddress; // string
  public $ReplyToDisplayName; // string
}

class ExactTarget_TriggeredSendExclusionList {
}

class ExactTarget_TriggeredSendTypeEnum {
  const Continuous='Continuous';
  const Batched='Batched';
  const Scheduled='Scheduled';
}

class ExactTarget_TriggeredSendStatusEnum {
  const _New='New';
  const Inactive='Inactive';
  const Active='Active';
  const Canceled='Canceled';
  const Deleted='Deleted';
  const Moved='Moved';
}

class ExactTarget_TriggeredSend {
  public $TriggeredSendDefinition; // ExactTarget_TriggeredSendDefinition
  public $Subscribers; // ExactTarget_Subscriber
  public $Attributes; // ExactTarget_Attribute
}

class ExactTarget_TriggeredSendCreateResult {
  public $SubscriberFailures; // ExactTarget_SubscriberResult
}

class ExactTarget_SubscriberResult {
  public $Subscriber; // ExactTarget_Subscriber
  public $ErrorCode; // string
  public $ErrorDescription; // string
  public $Ordinal; // int
}

class ExactTarget_SubscriberSendResult {
  public $Send; // ExactTarget_Send
  public $Email; // ExactTarget_Email
  public $Subscriber; // ExactTarget_Subscriber
  public $ClickDate; // dateTime
  public $BounceDate; // dateTime
  public $OpenDate; // dateTime
  public $SentDate; // dateTime
  public $LastAction; // string
  public $UnsubscribeDate; // dateTime
  public $FromAddress; // string
  public $FromName; // string
  public $TotalClicks; // int
  public $UniqueClicks; // int
  public $Subject; // string
  public $ViewSentEmailURL; // string
  public $HardBounces; // int
  public $SoftBounces; // int
  public $OtherBounces; // int
}

class ExactTarget_TriggeredSendSummary {
  public $TriggeredSendDefinition; // ExactTarget_TriggeredSendDefinition
  public $Sent; // long
  public $NotSentDueToOptOut; // long
  public $NotSentDueToUndeliverable; // long
  public $Bounces; // long
  public $Opens; // long
  public $Clicks; // long
  public $UniqueOpens; // long
  public $UniqueClicks; // long
  public $OptOuts; // long
  public $SurveyResponses; // long
  public $FTAFRequests; // long
  public $FTAFEmailsSent; // long
  public $FTAFOptIns; // long
  public $Conversions; // long
  public $UniqueConversions; // long
  public $InProcess; // long
  public $NotSentDueToError; // long
  public $Queued; // long
}

class ExactTarget_AsyncRequestResult {
  public $Status; // string
  public $CompleteDate; // dateTime
  public $CallStatus; // string
  public $CallMessage; // string
}

class ExactTarget_VoiceTriggeredSend {
  public $VoiceTriggeredSendDefinition; // ExactTarget_VoiceTriggeredSendDefinition
  public $Subscriber; // ExactTarget_Subscriber
  public $Message; // string
  public $Number; // string
  public $TransferMessage; // string
  public $TransferNumber; // string
}

class ExactTarget_VoiceTriggeredSendDefinition {
}

class ExactTarget_SMSTriggeredSend {
  public $SMSTriggeredSendDefinition; // ExactTarget_SMSTriggeredSendDefinition
  public $Subscriber; // ExactTarget_Subscriber
  public $Message; // string
  public $Number; // string
  public $FromAddress; // string
  public $SmsSendId; // string
}

class ExactTarget_SMSTriggeredSendDefinition {
  public $Publication; // ExactTarget_List
  public $DataExtension; // ExactTarget_DataExtension
  public $Content; // ExactTarget_ContentArea
  public $SendToList; // boolean
}

class ExactTarget_SendClassification {
  public $SendClassificationType; // ExactTarget_SendClassificationTypeEnum
  public $Name; // string
  public $Description; // string
  public $SenderProfile; // ExactTarget_SenderProfile
  public $DeliveryProfile; // ExactTarget_DeliveryProfile
  public $HonorPublicationListOptOutsForTransactionalSends; // boolean
  public $SendPriority; // ExactTarget_SendPriorityEnum
  public $ArchiveEmail; // boolean
}

class ExactTarget_SendClassificationTypeEnum {
  const Operational='Operational';
  const Marketing='Marketing';
}

class ExactTarget_SendPriorityEnum {
  const Burst='Burst';
  const Normal='Normal';
  const Low='Low';
}

class ExactTarget_SenderProfile {
  public $Name; // string
  public $Description; // string
  public $FromName; // string
  public $FromAddress; // string
  public $UseDefaultRMMRules; // boolean
  public $AutoForwardToEmailAddress; // string
  public $AutoForwardToName; // string
  public $DirectForward; // boolean
  public $AutoForwardTriggeredSend; // ExactTarget_TriggeredSendDefinition
  public $AutoReply; // boolean
  public $AutoReplyTriggeredSend; // ExactTarget_TriggeredSendDefinition
  public $SenderHeaderEmailAddress; // string
  public $SenderHeaderName; // string
  public $DataRetentionPeriodLength; // short
  public $DataRetentionPeriodUnitOfMeasure; // ExactTarget_RecurrenceTypeEnum
  public $ReplyManagementRuleSet; // ExactTarget_APIObject
  public $ReplyToAddress; // string
  public $ReplyToDisplayName; // string
}

class ExactTarget_DeliveryProfile {
  public $Name; // string
  public $Description; // string
  public $SourceAddressType; // ExactTarget_DeliveryProfileSourceAddressTypeEnum
  public $PrivateIP; // ExactTarget_PrivateIP
  public $DomainType; // ExactTarget_DeliveryProfileDomainTypeEnum
  public $PrivateDomain; // ExactTarget_PrivateDomain
  public $HeaderSalutationSource; // ExactTarget_SalutationSourceEnum
  public $HeaderContentArea; // ExactTarget_ContentArea
  public $FooterSalutationSource; // ExactTarget_SalutationSourceEnum
  public $FooterContentArea; // ExactTarget_ContentArea
  public $SubscriberLevelPrivateDomain; // boolean
  public $SMIMESignatureCertificate; // ExactTarget_Certificate
  public $PrivateDomainSet; // ExactTarget_PrivateDomainSet
}

class ExactTarget_DeliveryProfileSourceAddressTypeEnum {
  const DefaultPrivateIPAddress='DefaultPrivateIPAddress';
  const CustomPrivateIPAddress='CustomPrivateIPAddress';
}

class ExactTarget_DeliveryProfileDomainTypeEnum {
  const DefaultDomain='DefaultDomain';
  const CustomDomain='CustomDomain';
}

class ExactTarget_SalutationSourceEnum {
  const _Default='Default';
  const ContentLibrary='ContentLibrary';
  const None='None';
}

class ExactTarget_PrivateDomain {
}

class ExactTarget_PrivateDomainSet {
}

class ExactTarget_PrivateIP {
  public $Name; // string
  public $Description; // string
  public $IsActive; // boolean
  public $OrdinalID; // short
  public $IPAddress; // string
}

class ExactTarget_SendDefinition {
  public $CategoryID; // int
  public $SendClassification; // ExactTarget_SendClassification
  public $SenderProfile; // ExactTarget_SenderProfile
  public $FromName; // string
  public $FromAddress; // string
  public $DeliveryProfile; // ExactTarget_DeliveryProfile
  public $SourceAddressType; // ExactTarget_DeliveryProfileSourceAddressTypeEnum
  public $PrivateIP; // ExactTarget_PrivateIP
  public $DomainType; // ExactTarget_DeliveryProfileDomainTypeEnum
  public $PrivateDomain; // ExactTarget_PrivateDomain
  public $HeaderSalutationSource; // ExactTarget_SalutationSourceEnum
  public $HeaderContentArea; // ExactTarget_ContentArea
  public $FooterSalutationSource; // ExactTarget_SalutationSourceEnum
  public $FooterContentArea; // ExactTarget_ContentArea
  public $SuppressTracking; // boolean
  public $IsSendLogging; // boolean
}

class ExactTarget_AudienceItem {
  public $List; // ExactTarget_List
  public $SendDefinitionListType; // ExactTarget_SendDefinitionListTypeEnum
  public $CustomObjectID; // string
  public $DataSourceTypeID; // ExactTarget_DataSourceTypeEnum
}

class ExactTarget_EmailSendDefinition {
  public $SendDefinitionList; // ExactTarget_SendDefinitionList
  public $Email; // ExactTarget_Email
  public $BccEmail; // string
  public $AutoBccEmail; // string
  public $TestEmailAddr; // string
  public $EmailSubject; // string
  public $DynamicEmailSubject; // string
  public $IsMultipart; // boolean
  public $IsWrapped; // boolean
  public $SendLimit; // int
  public $SendWindowOpen; // time
  public $SendWindowClose; // time
  public $SendWindowDelete; // boolean
  public $DeduplicateByEmail; // boolean
  public $ExclusionFilter; // string
  public $TrackingUsers; // ExactTarget_TrackingUsers
  public $Additional; // string
  public $CCEmail; // string
  public $DeliveryScheduledTime; // time
  public $MessageDeliveryType; // ExactTarget_MessageDeliveryTypeEnum
  public $IsSeedListSend; // boolean
  public $TimeZone; // ExactTarget_TimeZone
  public $SeedListOccurance; // int
  public $PreHeader; // string
  public $ReplyToAddress; // string
  public $ReplyToDisplayName; // string
}

class ExactTarget_TrackingUsers {
  public $TrackingUser; // ExactTarget_TrackingUser
}

class ExactTarget_SendDefinitionList {
  public $FilterDefinition; // ExactTarget_FilterDefinition
  public $IsTestObject; // boolean
  public $SalesForceObjectID; // string
  public $Name; // string
  public $Parameters; // ExactTarget_Parameters
}


class ExactTarget_SendDefinitionStatusEnum {
  const Active='Active';
  const Archived='Archived';
  const Deleted='Deleted';
}

class ExactTarget_SendDefinitionListTypeEnum {
  const SourceList='SourceList';
  const ExclusionList='ExclusionList';
  const DomainExclusion='DomainExclusion';
  const OptOutList='OptOutList';
}

class ExactTarget_DataSourceTypeEnum {
  const _List='List';
  const CustomObject='CustomObject';
  const DomainExclusion='DomainExclusion';
  const SalesForceReport='SalesForceReport';
  const SalesForceCampaign='SalesForceCampaign';
  const FilterDefinition='FilterDefinition';
  const OptOutList='OptOutList';
}

class ExactTarget_MessageDeliveryTypeEnum {
  const Standard='Standard';
  const DelayedDeliveryByMTAQueue='DelayedDeliveryByMTAQueue';
  const DelayedDeliveryByOMMQueue='DelayedDeliveryByOMMQueue';
}

class ExactTarget_TrackingUser {
  public $IsActive; // boolean
  public $EmployeeID; // int
}

class ExactTarget_MessagingVendorKind {
  public $Vendor; // string
  public $Kind; // string
  public $IsUsernameRequired; // boolean
  public $IsPasswordRequired; // boolean
  public $IsProfileRequired; // boolean
}

class ExactTarget_MessagingConfiguration {
  public $Code; // string
  public $MessagingVendorKind; // ExactTarget_MessagingVendorKind
  public $IsActive; // boolean
  public $Url; // string
  public $UserName; // string
  public $Password; // string
  public $ProfileID; // string
  public $CallbackUrl; // string
  public $MediaTypes; // string
}

class ExactTarget_SMSMTEvent {
  public $SMSTriggeredSend; // ExactTarget_SMSTriggeredSend
  public $Subscriber; // ExactTarget_Subscriber
  public $MOCode; // string
  public $EventDate; // dateTime
  public $Carrier; // string
}

class ExactTarget_SMSMOEvent {
  public $Keyword; // ExactTarget_BaseMOKeyword
  public $MobileTelephoneNumber; // string
  public $MOCode; // string
  public $EventDate; // dateTime
  public $MOMessage; // string
  public $MTMessage; // string
  public $Carrier; // string
}

class ExactTarget_BaseMOKeyword {
  public $IsDefaultKeyword; // boolean
}

class ExactTarget_SendSMSMOKeyword {
  public $NextMOKeyword; // ExactTarget_BaseMOKeyword
  public $Message; // string
  public $ScriptErrorMessage; // string
}

class ExactTarget_UnsubscribeFromSMSPublicationMOKeyword {
  public $NextMOKeyword; // ExactTarget_BaseMOKeyword
  public $AllUnsubSuccessMessage; // string
  public $InvalidPublicationMessage; // string
  public $SingleUnsubSuccessMessage; // string
}

class ExactTarget_DoubleOptInMOKeyword {
  public $DefaultPublication; // ExactTarget_List
  public $InvalidPublicationMessage; // string
  public $InvalidResponseMessage; // string
  public $MissingPublicationMessage; // string
  public $NeedPublicationMessage; // string
  public $PromptMessage; // string
  public $SuccessMessage; // string
  public $UnexpectedErrorMessage; // string
  public $ValidPublications; // ExactTarget_ValidPublications
  public $ValidResponses; // ExactTarget_ValidResponses
}

class ExactTarget_ValidPublications {
  public $ValidPublication; // ExactTarget_List
}

class ExactTarget_ValidResponses {
  public $ValidResponse; // string
}

class ExactTarget_HelpMOKeyword {
  public $FriendlyName; // string
  public $DefaultHelpMessage; // string
  public $MenuText; // string
  public $MoreChoicesPrompt; // string
}

class ExactTarget_SendEmailMOKeyword {
  public $SuccessMessage; // string
  public $MissingEmailMessage; // string
  public $FailureMessage; // string
  public $TriggeredSend; // ExactTarget_TriggeredSendDefinition
  public $NextMOKeyword; // ExactTarget_BaseMOKeyword
}

class ExactTarget_SMSSharedKeyword {
  public $ShortCode; // string
  public $SharedKeyword; // string
  public $RequestDate; // dateTime
  public $EffectiveDate; // dateTime
  public $ExpireDate; // dateTime
  public $ReturnToPoolDate; // dateTime
  public $CountryCode; // string
}

class ExactTarget_UserMap {
  public $ETAccountUser; // ExactTarget_AccountUser
  public $AdditionalData; // ExactTarget_APIProperty
}

class ExactTarget_Folder {
  public $ID; // int
  public $ParentID; // int
}

class ExactTarget_FileTransferLocation {
}

class ExactTarget_DataExtractActivity {
}

class ExactTarget_MessageSendActivity {
}

class ExactTarget_SmsSendActivity {
}

class ExactTarget_MobileConnectRefreshListActivity {
}

class ExactTarget_MobileConnectSendSmsActivity {
}

class ExactTarget_MobilePushSendMessageActivity {
}

class ExactTarget_ReportActivity {
}

class ExactTarget_DataExtension {
  public $Name; // string
  public $Description; // string
  public $IsSendable; // boolean
  public $IsTestable; // boolean
  public $SendableDataExtensionField; // ExactTarget_DataExtensionField
  public $SendableSubscriberField; // ExactTarget_Attribute
  public $Template; // ExactTarget_DataExtensionTemplate
  public $DataRetentionPeriodLength; // int
  public $DataRetentionPeriodUnitOfMeasure; // int
  public $RowBasedRetention; // boolean
  public $ResetRetentionPeriodOnImport; // boolean
  public $DeleteAtEndOfRetentionPeriod; // boolean
  public $RetainUntil; // string
  public $Fields; // ExactTarget_Fields
  public $DataRetentionPeriod; // ExactTarget_DateTimeUnitOfMeasure
  public $CategoryID; // long
  public $Status; // string
}

class ExactTarget_Fields {
  public $Field; // ExactTarget_DataExtensionField
}

class ExactTarget_DataExtensionField {
  public $Ordinal; // int
  public $IsPrimaryKey; // boolean
  public $FieldType; // ExactTarget_DataExtensionFieldType
  public $DataExtension; // ExactTarget_DataExtension
}

class ExactTarget_DataExtensionFieldType {
  const Text='Text';
  const Number='Number';
  const Date='Date';
  const Boolean='Boolean';
  const EmailAddress='EmailAddress';
  const Phone='Phone';
  const Decimal='Decimal';
  const Locale='Locale';
}

class ExactTarget_DateTimeUnitOfMeasure {
  const Days='Days';
  const Weeks='Weeks';
  const Months='Months';
  const Years='Years';
}

class ExactTarget_DataExtensionTemplate {
  public $Name; // string
  public $Description; // string
}

class ExactTarget_DataExtensionObject {
  public $Name; // string
  public $Keys; // ExactTarget_Keys
}

class ExactTarget_Keys {
  public $Key; // ExactTarget_APIProperty
}

class ExactTarget_DataExtensionError {
  public $Name; // string
  public $ErrorCode; // integer
  public $ErrorMessage; // string
}

class ExactTarget_DataExtensionCreateResult {
  public $ErrorMessage; // string
  public $KeyErrors; // ExactTarget_KeyErrors
  public $ValueErrors; // ExactTarget_ValueErrors
}

class ExactTarget_KeyErrors {
  public $KeyError; // ExactTarget_DataExtensionError
}

class ExactTarget_ValueErrors {
  public $ValueError; // ExactTarget_DataExtensionError
}

class ExactTarget_DataExtensionUpdateResult {
  public $ErrorMessage; // string
  public $KeyErrors; // ExactTarget_KeyErrors
  public $ValueErrors; // ExactTarget_ValueErrors
}



class ExactTarget_DataExtensionDeleteResult {
  public $ErrorMessage; // string
  public $KeyErrors; // ExactTarget_KeyErrors
}


class ExactTarget_FileType {
  const CSV='CSV';
  const TAB='TAB';
  const Other='Other';
}

class ExactTarget_ImportDefinitionSubscriberImportType {
  const Email='Email';
  const SMS='SMS';
}

class ExactTarget_ImportDefinitionUpdateType {
  const AddAndUpdate='AddAndUpdate';
  const AddAndDoNotUpdate='AddAndDoNotUpdate';
  const UpdateButDoNotAdd='UpdateButDoNotAdd';
  const Merge='Merge';
  const Overwrite='Overwrite';
  const ColumnBased='ColumnBased';
}

class ExactTarget_ImportDefinitionColumnBasedAction {
  public $Value; // string
  public $Action; // ExactTarget_ImportDefinitionColumnBasedActionType
}

class ExactTarget_ImportDefinitionColumnBasedActionType {
  const AddAndUpdate='AddAndUpdate';
  const AddButDoNotUpdate='AddButDoNotUpdate';
  const Delete='Delete';
  const Skip='Skip';
  const UpdateButDoNotAdd='UpdateButDoNotAdd';
}

class ExactTarget_ImportDefinitionFieldMappingType {
  const InferFromColumnHeadings='InferFromColumnHeadings';
  const MapByOrdinal='MapByOrdinal';
  const ManualMap='ManualMap';
}

class ExactTarget_FieldMap {
  public $SourceName; // string
  public $SourceOrdinal; // int
  public $DestinationName; // string
}

class ExactTarget_ImportDefinitionAutoGenerateDestination {
  public $DataExtensionTarget; // ExactTarget_DataExtension
  public $ErrorIfExists; // boolean
}

class ExactTarget_ImportDefinition {
  public $AllowErrors; // boolean
  public $DestinationObject; // ExactTarget_APIObject
  public $FieldMappingType; // ExactTarget_ImportDefinitionFieldMappingType
  public $FieldMaps; // ExactTarget_FieldMaps
  public $FileSpec; // string
  public $FileType; // ExactTarget_FileType
  public $Notification; // ExactTarget_AsyncResponse
  public $RetrieveFileTransferLocation; // ExactTarget_FileTransferLocation
  public $SubscriberImportType; // ExactTarget_ImportDefinitionSubscriberImportType
  public $UpdateType; // ExactTarget_ImportDefinitionUpdateType
  public $MaxFileAge; // int
  public $MaxFileAgeScheduleOffset; // int
  public $MaxImportFrequency; // int
  public $Delimiter; // string
  public $HeaderLines; // int
  public $AutoGenerateDestination; // ExactTarget_ImportDefinitionAutoGenerateDestination
  public $ControlColumn; // string
  public $ControlColumnDefaultAction; // ExactTarget_ImportDefinitionColumnBasedActionType
  public $ControlColumnActions; // ExactTarget_ControlColumnActions
  public $EndOfLineRepresentation; // string
  public $NullRepresentation; // string
  public $StandardQuotedStrings; // boolean
  public $Filter; // string
  public $DateFormattingLocale; // ExactTarget_Locale
  public $DeleteFile; // boolean
  public $SourceObject; // ExactTarget_APIObject
  public $DestinationType; // int
  public $SubscriptionDefinitionId; // string
  public $EncodingCodePage; // int
}

class ExactTarget_FieldMaps {
  public $FieldMap; // ExactTarget_FieldMap
}

class ExactTarget_ControlColumnActions {
  public $ControlColumnAction; // ExactTarget_ImportDefinitionColumnBasedAction
}

class ExactTarget_ImportDefinitionFieldMap {
  public $SourceName; // string
  public $SourceOrdinal; // int
  public $DestinationName; // string
}

class ExactTarget_ImportResultsSummary {
  public $ImportDefinitionCustomerKey; // string
  public $StartDate; // string
  public $EndDate; // string
  public $DestinationID; // string
  public $NumberSuccessful; // int
  public $NumberDuplicated; // int
  public $NumberErrors; // int
  public $TotalRows; // int
  public $ImportType; // string
  public $ImportStatus; // string
  public $TaskResultID; // int
}

class ExactTarget_FilterDefinition {
  public $Name; // string
  public $Description; // string
  public $DataSource; // ExactTarget_APIObject
  public $DataFilter; // ExactTarget_FilterPart
  public $CategoryID; // int
}

class ExactTarget_GroupDefinition {
}

class ExactTarget_FileTransferActivity {
}

class ExactTarget_ListSend {
  public $SendID; // int
  public $List; // ExactTarget_List
  public $Duplicates; // int
  public $InvalidAddresses; // int
  public $ExistingUndeliverables; // int
  public $ExistingUnsubscribes; // int
  public $HardBounces; // int
  public $SoftBounces; // int
  public $OtherBounces; // int
  public $ForwardedEmails; // int
  public $UniqueClicks; // int
  public $UniqueOpens; // int
  public $NumberSent; // int
  public $NumberDelivered; // int
  public $Unsubscribes; // int
  public $MissingAddresses; // int
  public $PreviewURL; // string
  public $Links; // ExactTarget_Link
  public $Events; // ExactTarget_TrackingEvent
}

class ExactTarget_LinkSend {
  public $SendID; // int
  public $Link; // ExactTarget_Link
}

class ExactTarget_ObjectExtension {
  public $Type; // string
  public $Properties; // ExactTarget_Properties
}

class ExactTarget_Properties {
  public $Property; // ExactTarget_APIProperty
}

class ExactTarget_PublicKeyManagement {
  public $Name; // string
  public $Key; // base64Binary
}

class ExactTarget_SecurityObject {
}

class ExactTarget_Certificate {
}

class ExactTarget_SystemStatusOptions {
}

class ExactTarget_SystemStatusRequestMsg {
  public $Options; // ExactTarget_SystemStatusOptions
}

class ExactTarget_SystemStatusResult {
  public $SystemStatus; // ExactTarget_SystemStatusType
  public $Outages; // ExactTarget_Outages
}

class ExactTarget_Outages {
  public $Outage; // ExactTarget_SystemOutage
}

class ExactTarget_SystemStatusResponseMsg {
  public $Results; // ExactTarget_Results
  public $OverallStatus; // string
  public $OverallStatusMessage; // string
  public $RequestID; // string
}


class ExactTarget_SystemStatusType {
  const OK='OK';
  const UnplannedOutage='UnplannedOutage';
  const InMaintenance='InMaintenance';
}

class ExactTarget_SystemOutage {
}

class ExactTarget_Authentication {
}

class ExactTarget_UsernameAuthentication {
  public $UserName; // string
  public $PassWord; // string
}

class ExactTarget_ResourceSpecification {
  public $URN; // string
  public $Authentication; // ExactTarget_Authentication
}

class ExactTarget_Portfolio {
  public $Source; // ExactTarget_ResourceSpecification
  public $CategoryID; // int
  public $FileName; // string
  public $DisplayName; // string
  public $Description; // string
  public $TypeDescription; // string
  public $IsUploaded; // boolean
  public $IsActive; // boolean
  public $FileSizeKB; // int
  public $ThumbSizeKB; // int
  public $FileWidthPX; // int
  public $FileHeightPX; // int
  public $FileURL; // string
  public $ThumbURL; // string
  public $CacheClearTime; // dateTime
  public $CategoryType; // string
}

class ExactTarget_Template {
  public $TemplateName; // string
  public $LayoutHTML; // string
  public $BackgroundColor; // string
  public $BorderColor; // string
  public $BorderWidth; // int
  public $Cellpadding; // int
  public $Cellspacing; // int
  public $Width; // int
  public $Align; // string
  public $ActiveFlag; // int
  public $CategoryID; // int
  public $CategoryType; // string
  public $OwnerID; // int
  public $HeaderContent; // ExactTarget_ContentArea
  public $Layout; // ExactTarget_Layout
  public $TemplateSubject; // string
  public $IsTemplateSubjectLocked; // boolean
  public $PreHeader; // string
}

class ExactTarget_Layout {
  public $LayoutName; // string
}

class ExactTarget_QueryDefinition {
  public $QueryText; // string
  public $TargetType; // string
  public $DataExtensionTarget; // ExactTarget_InteractionBaseObject
  public $TargetUpdateType; // string
  public $FileSpec; // string
  public $FileType; // string
  public $Status; // string
  public $CategoryID; // int
}

class ExactTarget_HiveQueryDefinition {
  public $QueryDefinition; // string
  public $Status; // string
  public $CategoryID; // int
}

class ExactTarget_IntegrationProfile {
  public $ProfileID; // string
  public $SubscriberKey; // string
  public $ExternalID; // string
  public $ExternalType; // string
}

class ExactTarget_IntegrationProfileDefinition {
  public $ProfileID; // string
  public $Name; // string
  public $Description; // string
  public $ExternalSystemType; // int
}

class ExactTarget_ReplyMailManagementConfiguration {
  public $EmailDisplayName; // string
  public $ReplySubdomain; // string
  public $EmailReplyAddress; // string
  public $DNSRedirectComplete; // boolean
  public $DeleteAutoReplies; // boolean
  public $SupportUnsubscribes; // boolean
  public $SupportUnsubKeyword; // boolean
  public $SupportUnsubscribeKeyword; // boolean
  public $SupportRemoveKeyword; // boolean
  public $SupportOptOutKeyword; // boolean
  public $SupportLeaveKeyword; // boolean
  public $SupportMisspelledKeywords; // boolean
  public $SendAutoReplies; // boolean
  public $AutoReplySubject; // string
  public $AutoReplyBody; // string
  public $ForwardingAddress; // string
}

class ExactTarget_FileTrigger {
  public $ExternalReference; // string
  public $Type; // string
  public $Status; // string
  public $StatusMessage; // string
  public $RequestParameterDetail; // string
  public $ResponseControlManifest; // string
  public $FileName; // string
  public $Description; // string
  public $Name; // string
  public $LastPullDate; // dateTime
  public $ScheduledDate; // dateTime
  public $IsActive; // boolean
  public $FileTriggerProgramID; // string
}

class ExactTarget_FileTriggerTypeLastPull {
  public $ExternalReference; // string
  public $Type; // string
  public $LastPullDate; // dateTime
}

class ExactTarget_ProgramManifestTemplate {
  public $Type; // string
  public $OperationType; // string
  public $Content; // string
}

class ExactTarget_SubscriberAddress {
  public $AddressType; // string
  public $Address; // string
  public $Statuses; // ExactTarget_Statuses
}

class ExactTarget_Statuses {
  public $Status; // ExactTarget_AddressStatus
}

class ExactTarget_SMSAddress {
  public $Carrier; // string
}

class ExactTarget_EmailAddress {
  public $Type; // ExactTarget_EmailType
}

class ExactTarget_AddressStatus {
  public $Status; // ExactTarget_SubscriberAddressStatus
}

class ExactTarget_SubscriberAddressStatus {
  const OptedIn='OptedIn';
  const OptedOut='OptedOut';
  const InActive='InActive';
}

class ExactTarget_Publication {
  public $Name; // string
  public $IsActive; // boolean
  public $SendClassification; // ExactTarget_SendClassification
  public $Subscribers; // ExactTarget_Subscribers
  public $Category; // int
}


class ExactTarget_PublicationSubscriber {
  public $Publication; // ExactTarget_Publication
  public $Subscriber; // ExactTarget_Subscriber
}

class ExactTarget_Automation {
  public $Schedule; // ExactTarget_ScheduleDefinition
  public $AutomationTasks; // ExactTarget_AutomationTasks
  public $IsActive; // boolean
  public $AutomationSource; // ExactTarget_AutomationSource
  public $Status; // int
  public $Notifications; // ExactTarget_Notifications
  public $ScheduledTime; // dateTime
  public $AutomationType; // string
}

class ExactTarget_AutomationTasks {
  public $AutomationTask; // ExactTarget_AutomationTask
}

class ExactTarget_Notifications {
  public $Notification; // ExactTarget_AutomationNotification
}

class ExactTarget_AutomationSource {
  public $AutomationSourceID; // string
  public $AutomationSourceType; // string
}

class ExactTarget_AutomationInstances {
  public $InstanceCount; // int
  public $AutomationInstanceCollection; // ExactTarget_AutomationInstanceCollection
}

class ExactTarget_AutomationInstanceCollection {
  public $AutomationInstance; // ExactTarget_AutomationInstance
}

class ExactTarget_AutomationInstance {
  public $AutomationID; // string
  public $StatusMessage; // string
  public $StatusLastUpdate; // dateTime
  public $TaskInstances; // ExactTarget_TaskInstances
  public $StartTime; // dateTime
  public $CompletedTime; // dateTime
}

class ExactTarget_TaskInstances {
  public $AutomationTaskInstance; // ExactTarget_AutomationTaskInstance
}

class ExactTarget_AutomationNotification {
  public $Address; // string
  public $Body; // string
  public $ChannelType; // string
  public $NotificationType; // string
  public $AutomationID; // string
}

class ExactTarget_AutomationTask {
  public $AutomationTaskType; // string
  public $Name; // string
  public $Description; // string
  public $Automation; // ExactTarget_Automation
  public $Sequence; // int
  public $Activities; // ExactTarget_Activities
}

class ExactTarget_Activities {
  public $Activity; // ExactTarget_AutomationActivity
}

class ExactTarget_AutomationTaskInstance {
  public $StepDefinition; // ExactTarget_AutomationTask
  public $AutomationInstance; // ExactTarget_AutomationInstance
  public $ActivityInstances; // ExactTarget_ActivityInstances
}

class ExactTarget_ActivityInstances {
  public $ActivityInstance; // ExactTarget_AutomationActivityInstance
}

class ExactTarget_AutomationActivity {
  public $Name; // string
  public $Description; // string
  public $IsActive; // boolean
  public $Definition; // ExactTarget_APIObject
  public $Automation; // ExactTarget_Automation
  public $AutomationTask; // ExactTarget_AutomationTask
  public $Sequence; // int
  public $ActivityObject; // ExactTarget_APIObject
}

class ExactTarget_AutomationActivityInstance {
  public $ActivityID; // string
  public $AutomationID; // string
  public $SequenceID; // int
  public $Status; // int
  public $StatusLastUpdate; // dateTime
  public $StatusMessage; // string
  public $ActivityDefinition; // ExactTarget_AutomationActivity
  public $AutomationInstance; // ExactTarget_AutomationInstance
  public $AutomationTaskInstance; // ExactTarget_AutomationTaskInstance
  public $ScheduledTime; // dateTime
  public $StartTime; // dateTime
  public $CompletedTime; // dateTime
}

class ExactTarget_AutomationTestType {
  const OK='OK';
  const UnplannedOutage='UnplannedOutage';
  const InMaintenance='InMaintenance';
}

class ExactTarget_AutomationTypes {
  const scheduled='scheduled';
  const triggered='triggered';
}

class ExactTarget_AutomationSourceTypes {
  const Unknown='Unknown';
  const FileTrigger='FileTrigger';
  const UserInterface='UserInterface';
  const UserAPI='UserAPI';
  const RESTApi='RESTApi';
}

class ExactTarget_AutomationStatus {
  const Error='Error';
  const BuildingError='BuildingError';
  const Building='Building';
  const Ready='Ready';
  const Running='Running';
  const Paused='Paused';
  const Stopped='Stopped';
  const Scheduled='Scheduled';
  const AwaitingTrigger='AwaitingTrigger';
  const InactiveTrigger='InactiveTrigger';
  const Skipped='Skipped';
  const Unknown='Unknown';
  const _New='New';
}

class ExactTarget_PlatformApplication {
  public $Package; // ExactTarget_PlatformApplicationPackage
  public $Packages; // ExactTarget_PlatformApplicationPackage
  public $ResourceSpecification; // ExactTarget_ResourceSpecification
  public $DeveloperVersion; // string
}

class ExactTarget_PlatformApplicationPackage {
  public $ResourceSpecification; // ExactTarget_ResourceSpecification
  public $SigningKey; // ExactTarget_PublicKeyManagement
  public $IsUpgrade; // boolean
  public $DeveloperVersion; // string
}

class ExactTarget_SuppressionListDefinition {
  public $Name; // string
  public $Category; // long
  public $Description; // string
  public $Contexts; // ExactTarget_Contexts
  public $Fields; // ExactTarget_Fields
  public $SubscriberCount; // long
  public $NotifyEmail; // string
}

class ExactTarget_Contexts {
  public $Context; // ExactTarget_SuppressionListContext
}


class ExactTarget_SuppressionListContext {
  public $Context; // ExactTarget_SuppressionListContextEnum
  public $SendClassificationType; // ExactTarget_SendClassificationTypeEnum
  public $SendClassification; // ExactTarget_SendClassification
  public $Send; // ExactTarget_Send
  public $Definition; // ExactTarget_SuppressionListDefinition
  public $AppliesToAllSends; // boolean
  public $SenderProfile; // ExactTarget_SenderProfile
}

class ExactTarget_SuppressionListContextEnum {
  const Enterprise='Enterprise';
  const BusinessUnit='BusinessUnit';
  const SendClassification='SendClassification';
  const Send='Send';
  const _Global='Global';
  const SenderProfile='SenderProfile';
}

class ExactTarget_SuppressionListData {
  public $Properties; // ExactTarget_Properties
}


class ExactTarget_SendAdditionalAttribute {
  public $Email; // ExactTarget_Email
  public $Name; // string
  public $Value; // string
}

class ExactTarget_ImportFileDestination {
  public $TemplateCustomObject; // ExactTarget_DataExtension
  public $FileTransferLocation; // ExactTarget_FileTransferLocation
  public $FileSpec; // string
  public $EncodingCodePage; // int
  public $HasColumnHeader; // boolean
  public $FieldDelimiter; // string
  public $RowDelimiter; // string
  public $NullValue; // string
  public $BooleanFormat; // string
  public $DateTimeFormat; // string
  public $StringIdentifier; // string
  public $EscapeSequence; // string
}

class ExactTarget_ContactEvent {
  public $ContactID; // long
  public $ContactKey; // string
  public $EventDefinitionKey; // string
  public $Data; // ExactTarget_Data
}

class ExactTarget_Data {
  public $AttributeSet; // ExactTarget_AttributeSet
}

class ExactTarget_AttributeSet {
  public $Id; // string
  public $Key; // string
  public $Name; // string
  public $Items; // ExactTarget_Items
}

class ExactTarget_Items {
  public $Item; // ExactTarget_AttributeValueContainer
}

class ExactTarget_AttributeValueContainer {
  public $Values; // ExactTarget_Values
}


class ExactTarget_AttributeValue {
  public $Id; // string
  public $Key; // string
  public $Name; // string
  public $Value; // string
}

class ExactTarget_ContactEventCreateResult {
  public $EventInstanceID; // string
  public $AsyncRequestID; // long
}

?>
