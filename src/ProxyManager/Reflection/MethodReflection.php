<?php

/**
 * Rangine Aspect
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Aspect\ProxyManager\Reflection;

use function array_shift;
use function array_slice;
use function class_exists;
use function count;
use function file;
use function file_exists;
use function implode;
use function is_array;
use function rtrim;
use function token_get_all;
use function token_name;

use const FILE_IGNORE_NEW_LINES;

class MethodReflection extends \Laminas\Code\Reflection\MethodReflection {
	/**
	 * Tokenize method string and return concatenated body
	 *
	 * @param bool $bodyOnly
	 * @return string
	 */
	protected function extractMethodContents($bodyOnly = false) {
		$fileName = $this->getFileName();

		if ((class_exists($this->class) && false === $fileName) || ! file_exists($fileName)) {
			return '';
		}

		$lines = array_slice(
			file($fileName, FILE_IGNORE_NEW_LINES),
			$this->getStartLine() - 1,
			$this->getEndLine() - ($this->getStartLine() - 1),
			true
		);

		$functionLine = implode("\n", $lines);
		$tokens       = token_get_all('<?php ' . $functionLine);

		//remove first entry which is php open tag
		array_shift($tokens);

		if (! count($tokens)) {
			return '';
		}

		$capture    = false;
		$firstBrace = false;
		$body       = '';

		foreach ($tokens as $key => $token) {
			$tokenType  = is_array($token) ? token_name($token[0]) : $token;
			$tokenValue = is_array($token) ? $token[1] : $token;

			switch ($tokenType) {
				case 'T_FINAL':
				case 'T_ABSTRACT':
				case 'T_PUBLIC':
				case 'T_PROTECTED':
				case 'T_PRIVATE':
				case 'T_FUNCTION':
					// check to see if we have a valid function
					// then check if we are inside function and have a closure
					if ($this->isValidFunction($tokens, $key, $this->getName())) {
						if ($bodyOnly === false) {
							//if first instance of tokenType grab prefixed whitespace
							//and append to body
							if ($capture === false) {
								$body .= $this->extractPrefixedWhitespace($tokens, $key);
							}
							$body .= $tokenValue;
						}

						$capture = true;
					} else {
						//closure test
						if ($firstBrace && $tokenType == 'T_FUNCTION') {
							$body .= $tokenValue;
							break;
						}
						$capture = false;
						break;
					}
					break;

				case '{':
					if ($capture === false) {
						break;
					}

					if ($firstBrace === false) {
						$firstBrace = true;
						if ($bodyOnly === true) {
							break;
						}
					}

					$body .= $tokenValue;
					break;

				case '}':
					if ($capture === false) {
						break;
					}

					//check to see if this is the last brace
					if ($this->isEndingBrace($tokens, $key)) {
						//capture the end brace if not bodyOnly
						if ($bodyOnly === false) {
							$body .= $tokenValue;
						}

						break 2;
					}

					$body .= $tokenValue;
					break;

				default:
					if ($capture === false) {
						break;
					}

					// if returning body only wait for first brace before capturing
					if ($bodyOnly === true && $firstBrace !== true) {
						break;
					}

					$body .= $tokenValue;
					break;
			}
		}

		//remove ending whitespace and return
		return rtrim($body);
	}
}
