<?php
namespace ImageCompress;

class Files
{
	private $appRoot;                       // Absolute path of this app
	private $trustedDomains;                // ARRAY of trusted domains
	protected $originalsRoot;               // Root path to originals directory
	protected $compressedRoot;              // Root path to compressed directory

	private $originalFileUrl;               // URL of the new passed file
	private $urlPathArray;                  // PARSED URL array of this app
	private $filename;                      // Name of the image file (something.jpg)
	private $randomCompressedName;          // File name only of newly compressed image
	private $compressedFileUrlPath;         // URL path for new file
	private $savedFileName;                 // Absolute path of original file
	private $compressedFileName;            // Absolute path of new file


	function Files($originalFileUrl, $localFile, $trustedSourcesDomains) {
		$this->setAppRoot( $_SERVER['DOCUMENT_ROOT'] );
		$this->trustedDomains = $trustedSourcesDomains;
		$this->originalsRoot = $this->appRoot . "/imageCompress/originals/";
		$this->compressedRoot = $this->appRoot . "/imageCompress/compressed/";

		$this->buildFolderStructure();

		if ($localFile)
			$this->processLocalPath();
		else {
			$this->processUrlPath( $originalFileUrl );
		}
	}

	public function processUrlPath($originalFileUrl) {
		// Get the root URL so we can use the parts of it later
		$rootURL = $thisURL = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

		$this->urlPathArray = parse_url( $rootURL );
		$this->originalFileUrl = urldecode( $originalFileUrl );

		// Check image url and make sure the image is from a trusted source
		if ($this->checkImgSrc($this->trustedDomains)) {
			$urlArray = explode( "/", $this->originalFileUrl );
			$this->setFileName( $urlArray[ count( $urlArray ) - 1 ] );

			$this->setCompressedFilename();
			$this->setCompressedFileUrlPath();
			$this->setSavedFilename();

			// If the file already exists, don't pull it down again
			if ( ! file_exists( $this->getSavedFileName() ) ) {
				$this->getOriginalFile();
			}
		} else {
			throw new \Exception("Please use a trusted image source.");
		}
	}

	public function processLocalPath() {

		// TODO: Still need to do processing script for local files

	}

	/**
	 * Builds the folder structure in order to do the compression. Will build
	 * a folder for the original files (which are removed after processing)
	 * and a compressed folder to store the compressed files.
	 *
	 * @throws \Exception
	 */
	private function buildFolderStructure() {
		// See if these folders exist. If not, create them
		if (!file_exists($this->originalsRoot)) {
			if ( mkdir( $this->originalsRoot, 0766, true ) ) {
				// Make placeholder file (so folder doesn't get omitted if being versioned)
				$placeholderFile = fopen($this->originalsRoot . "README.md", "w");
				fwrite($placeholderFile, "Keep folder here for temporary storage of original files");
				fclose($placeholderFile);
			} else {
				throw new \Exception("Failed to create folder for original files");
			}
		}
		if (!file_exists($this->compressedRoot)) {
			if ( ! mkdir( $this->compressedRoot, 0766, TRUE ) ) {
				throw new \Exception( "Failed to create folder for compressed files" );
			}
		}
	}

	/**
	 * Uses the fileUrl that is passed into this object in order to download the image
	 * using CURL. This allows us to use the file and process it for compression.
	 *
	 * @throws \Exception
	 */
	public function getOriginalFile() {
		$cFile = curl_init($this->originalFileUrl);
		curl_setopt($cFile, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($cFile, CURLOPT_SSLVERSION,3);
		$data = curl_exec ($cFile);
		$error = curl_error($cFile);
		curl_close ($cFile);

		if ($error) {
			throw new \Exception($error);
		} else {
			$this->savedFileName = $this->originalsRoot . $this->filename;
			$file = fopen( $this->savedFileName, "w+" );
			fputs( $file, $data );
			fclose( $file );
		}
	}

	/**
	 * Checks for the existence of trusted sources in the host name.
	 *
	 * @param $trustedSources
	 *
	 * @return bool
	 */
	private function checkImgSrc($trustedSources) {
		if (empty($trustedSources)) {
			return true;
		} else {
			$originalFileURLArray = parse_url($this->originalFileUrl);

			$srcResult = false;

			foreach ($trustedSources as $source) {
				if (strpos($originalFileURLArray['host'], $source) !== false)
					$srcResult = true;
			}

			return $srcResult;
		}
	}


	/**
	 * Gets the App Root
	 *
	 * @return mixed
	 */
	public function getAppRoot() {
		return $this->appRoot;
	}

	/**
	 * Gets the filename
	 *
	 * @return mixed
	 */
	public function getFileName() {
		return $this->filename;
	}

	/**
	 * Gets the compressed filename
	 *
	 * @return mixed
	 */
	public function getCompressedFileName() {
		return $this->compressedFileName;
	}

	/**
	 * Gets the saved filename
	 *
	 * @return mixed
	 */
	public function getSavedFileName() {
		return $this->savedFileName;
	}

	/**
	 * Gets the URL path of the new compressed file
	 *
	 * @return mixed
	 */
	public function getCompressedFileUrlPath() {
		return $this->compressedFileUrlPath;
	}

	/**
	 * Sets the App Root
	 *
	 * @param $appRoot
	 */
	public function setAppRoot($appRoot) {
		$this->appRoot = $appRoot;
	}

	/**
	 * Sets the filename
	 *
	 * @param $filename
	 */
	public function setFileName($filename) {
		$this->filename = $filename;
	}

	/**
	 * Sets the compressed filename
	 */
	public function setCompressedFilename() {
		// Generate random number for filename add-on
		$this->randomCompressedName = number_format( microtime( TRUE ) / 3, 0, "", "" ) . "_" . $this->filename;
		$this->compressedFileName = $this->compressedRoot . $this->randomCompressedName;
	}

	/**
	 * Sets the saved filename
	 */
	public function setSavedFilename() {
		$this->savedFileName = realpath( $this->originalsRoot . $this->filename );
	}

	/**
	 * Sets the URL path of the new compressed file
	 */
	public function setCompressedFileUrlPath() {
		$this->compressedFileUrlPath = $this->urlPathArray['scheme'] . '://' . $this->urlPathArray['host'] . "/gcu/gcuedu/webservices/ajax/image-compress/images/compressed/" . $this->randomCompressedName;
	}
}