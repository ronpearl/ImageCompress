<?php
namespace ImageCompress;

class Compress Extends Files
{
	private $smallFileCompression = 75;         // Less compression
	private $largeFileCompression = 35;         // More compression
	private $trustedSourceDomains;              // ARRAY of trusted domain names
	private $removeOrigPostCompress = true;     // Remove original image after compressed??
	private $getBase64;                         // Bool to see if we should return Base64
	private $imageBase64;                       // Base64 of new file
	private $responseBuilder;                   // Class instance that builds the image data response array

	/**
	 * Compress constructor.
	 *
	 * Upon instantiating this class you can define the compression values for
	 * the small or larger files. This allows you to fine-tune the Image Magick
	 * settings for compression levels. Range is from 1-100, and the higher the
	 * compression setting means a lower compression value.
	 *
	 * This is based upon the image width being higher or lower than 800px. Small
	 * images are less than 800, larger images are wider than 800.
	 *
	 * @param array $trustedDomains
	 * @param int $smCompressionVal
	 * @param int $lrgCompressionVal
	 *
	 * @throws \Exception
	 */
	public function __construct($trustedDomains = [], $smCompressionVal = 75, $lrgCompressionVal = 35) {
		$this->smallFileCompression = $smCompressionVal;
		$this->largeFileCompression = $lrgCompressionVal;
		$this->trustedSourceDomains = $trustedDomains;

		$this->responseBuilder = new ResponseBuilder();   // Setup ResponseBuilder

		// Check if Image Magick extension is loaded
		if ( !extension_loaded('imagick') ) {
			throw new \Exception( "Image Magick extension is not loaded" );
		}
	}

	/**
	 * This method process the actual image compression and saves the file.
	 *
	 * @param $originalFileUrlOrPath
	 * @param bool $localFile
	 * @param bool $getBase64
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function doImageCompression($originalFileUrlOrPath, $localFile = true, $getBase64 = false) {
		parent::Files($originalFileUrlOrPath, $localFile, $this->trustedSourceDomains);
		$this->getBase64 = $getBase64;

		if ($this->getSavedFileName()) {
			$imagick = new \Imagick( realpath( $this->originalsRoot . $this->getFileName() ) );
			$imagick->setImageCompression( \Imagick::COMPRESSION_JPEG );

			$imageDimensions = $imagick->getImageGeometry();
			$quality = $imageDimensions['width'] >= 800 ? $this->largeFileCompression : $this->smallFileCompression;

			// Do image quality change
			if ( $imagick->setImageCompressionQuality( $quality ) ) {
				$imagick->writeImage( $this->getCompressedFileName() );

				// Check variable setting to see if we need to remove the saved original image or not
				if ($this->removeOrigPostCompress)
					unlink($this->getSavedFileName());

				// Set Base64 of new compressed image
				if ($this->getBase64)
					$this->setImageBase64();
			} else {
				throw new \Exception( "Compression Failed" );
			}
		} else {
			throw new \Exception("File does not exist");
		}

		// Build array of compressed image data
		$this->responseBuilder->setSuccessResponse(
			$this->getFileName(),
			$this->getCompressedFileUrlPath(),
			$quality,
			$this->getBase64 ? $this->getImageBase64() : ""
		);

		return json_encode( $this->responseBuilder->getResponse() );
	}

	/**
	 * Gets the base64 of the image
	 *
	 * @return mixed
	 */
	public function getImageBase64() {
		return $this->imageBase64;
	}

	/**
	 * Sets the base64 of the image
	 */
	private function setImageBase64() {
		$type = pathinfo($this->getCompressedFileName(), PATHINFO_EXTENSION);
		$data = file_get_contents($this->getCompressedFileName());
		$this->imageBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
	}
}