<?php

	class Image {

		public static $SUPPORTED_IMAGES = array(IMAGETYPE_JPEG, IMAGETYPE_PNG);

		private static function imageChecks($sourceFilePath, $destPath) {

			// Check GD is present
			if (!function_exists('getimagesize') || !function_exists('imagecreatetruecolor'))
				throw new ImageException("GD is not available", ImageException::$IMAGE_ERROR);

			// Check that the source file exists
			if (!file_exists($sourceFilePath))
				throw new ImageException("Image not found", ImageException::$IMAGE_NOT_FOUND);

			// Check the source file is readable
			if (!is_readable($sourceFilePath))
				throw new ImageException("Image not readable", ImageException::$IMAGE_NOT_READABLE);

			// Check the destination path if not create it
			if (!file_exists(dirname($destPath)) && mkdir(dirname($destPath), 0777, true))
				throw new ImageException("Destination path not writable", ImageException::$IMAGE_DESTINATION_NOT_WRITABLE);

		}

		public static function colourOverlay($sourceFilePath, $destPath, $red, $green, $blue, $opacity = 50) {

			self::imageChecks($sourceFilePath, $destPath);

			// Get the image information (width, height and type)
			$imageInfo = getimagesize($sourceFilePath);
			if ($imageInfo === FALSE)
				throw new ImageException("Image cannot be read or it is not an image", ImageException::$IMAGE_NOT_READABLE);

			$sourceWidth = $imageInfo[0];
			$sourceHeight = $imageInfo[1];
			$sourceType = $imageInfo[2];

			// Check that the image type is supported
			$validSource = FALSE;
			foreach (self::$SUPPORTED_IMAGES as $supportedImage) {
				if ($supportedImage === $sourceType) {
					$validSource = TRUE;
					break;
				}
			}
			if ($validSource === FALSE)
				throw new ImageException("Image type not supported", ImageException::$IMAGE_TYPE_NOT_SUPPORTED);

			// Get the image
			switch ($sourceType) {
				case IMAGETYPE_JPEG:
					$sourceImage = imagecreatefromjpeg($sourceFilePath);
					break;
				case IMAGETYPE_PNG:
					$sourceImage = imagecreatefrompng($sourceFilePath);
					break;
			}

			$opacity = round((100 / 127) * $opacity);
			$colour = imagecolorallocatealpha($sourceImage, $red, $green, $blue, $opacity);
			imagefilledrectangle($sourceImage, 0, 0, $sourceWidth - 1, $sourceHeight - 1, $colour);

			switch ($sourceType) {
				case IMAGETYPE_JPEG:
					if (!imagejpeg($sourceImage, $destPath, 100))
						throw new ImageException("Failed to write image", ImageException::$IMAGE_WRITE_FAILED);
					break;
				case IMAGETYPE_PNG:
					if (!imagepng($sourceImage, $destPath, 9))
						throw new ImageException("Failed to write image", ImageException::$IMAGE_WRITE_FAILED);
					break;
			}

			imagedestroy($sourceImage);

			return true;

		}

		public static function resize($sourceFilePath, $destPath, $imageWidth, $imageHeight, $preserveAspectRatio = true) {

			self::imageChecks($sourceFilePath, $destPath);

			// Get the image information (width, height and type)
			$imageInfo = getimagesize($sourceFilePath);
			if ($imageInfo === FALSE)
				throw new ImageException("Image cannot be read or it is not an image", ImageException::$IMAGE_NOT_READABLE);

			$sourceWidth = $imageInfo[0];
			$sourceHeight = $imageInfo[1];
			$sourceType = $imageInfo[2];

			// Check that the image type is supported
			$validSource = FALSE;
			foreach (self::$SUPPORTED_IMAGES as $supportedImage) {
				if ($supportedImage === $sourceType) {
					$validSource = TRUE;
					break;
				}
			}
			if ($validSource === FALSE)
				throw new ImageException("Image type not supported", ImageException::$IMAGE_TYPE_NOT_SUPPORTED);

			// Get the image
			switch ($sourceType) {
				case IMAGETYPE_JPEG:
					$sourceImage = @imagecreatefromjpeg($sourceFilePath);
					break;
				case IMAGETYPE_PNG:
					$sourceImage = @imagecreatefrompng($sourceFilePath);
					break;
			}

			if ($sourceImage == false)
				throw new ImageException("The source image cannot be read", ImageException::$IMAGE_NOT_READABLE);

			if ($preserveAspectRatio) {
				if ($imageWidth < $sourceWidth)
					$widthRatio = $imageWidth / $sourceWidth;
				else
					$widthRatio = $sourceWidth / $imageWidth;

				if ($imageHeight < $sourceHeight)
					$heightRatio = $imageHeight / $sourceHeight;
				else
					$heightRatio = $sourceHeight / $imageHeight;

				if ($widthRatio == $heightRatio && $widthRatio < 1) {
					$destWidth = round($sourceWidth * $widthRatio);
					$destHeight = round($sourceHeight * $widthRatio);
				} else if ($widthRatio < 1 || $heightRatio < 1) {
					if ($widthRatio < $heightRatio) {
						$destWidth = round($sourceWidth * $widthRatio);
						$destHeight = round($sourceHeight * $widthRatio);
					} else {
						$destWidth = round($sourceWidth * $heightRatio);
						$destHeight = round($sourceHeight * $heightRatio);
					}
				} else {
					$destWidth = $imageWidth;
					$destHeight = $imageHeight;
				}

				$xOffset = ($imageWidth / 2) - ($destWidth / 2);
				$yOffset = ($imageHeight / 2) - ($destHeight / 2);
			} else {
				$xOffset = 0;
				$yOffset = 0;

				// or actually...
				if ($imageWidth < $sourceWidth)
					$widthRatio = $imageWidth / $sourceWidth;
				else
					$widthRatio = $sourceWidth / $imageWidth;

				if ($imageHeight < $sourceHeight)
					$heightRatio = $imageHeight / $sourceHeight;
				else
					$heightRatio = $sourceHeight / $imageHeight;

				if ($widthRatio == $heightRatio && $widthRatio < 1) {
					$destWidth = round($sourceWidth * $widthRatio);
					$destHeight = round($sourceHeight * $widthRatio);
				} else if ($widthRatio < 1 || $heightRatio < 1) {
					if ($widthRatio < $heightRatio) {
						$destWidth = round($sourceWidth * $widthRatio);
						$destHeight = round($sourceHeight * $widthRatio);
					} else {
						$destWidth = round($sourceWidth * $heightRatio);
						$destHeight = round($sourceHeight * $heightRatio);
					}
				} else {
					$destWidth = $imageWidth;
					$destHeight = $imageHeight;
				}

			}

			$destImage = imagecreatetruecolor($destWidth, $destHeight);
			$backgroundColour = imagecolorallocate($sourceImage, 255, 255, 255);

			imagefilledrectangle($destImage, 0, 0, $imageWidth, $imageHeight, $backgroundColour);

			if ($sourceType == IMAGETYPE_PNG) {
				imagecolortransparent($destImage, imagecolorallocate($destImage, 0, 0, 0));
				imagealphablending($destImage, FALSE);
				imagesavealpha($destImage, TRUE);
			}

			if (imagecopyresampled($destImage, $sourceImage, $xOffset, $yOffset, 0, 0, $destWidth, $destHeight, $sourceWidth, $sourceHeight) == FALSE)
				throw new ImageException("Image creation failed", ImageException::$IMAGE_CREATION_FAILED);

			switch ($sourceType) {
				case IMAGETYPE_JPEG:
					if (!imagejpeg($destImage, $destPath, 100))
						throw new ImageException("Failed to write image", ImageException::$IMAGE_WRITE_FAILED);
					break;
				case IMAGETYPE_PNG:
					if (!imagepng($destImage, $destPath, 9))
						throw new ImageException("Failed to write image", ImageException::$IMAGE_WRITE_FAILED);
					break;
			}

			imagedestroy($sourceImage);
			imagedestroy($destImage);

			return true;

		}

	}

	class ImageException extends Exception {

		public static $IMAGE_ERROR = 999;
		public static $IMAGE_NOT_FOUND = 1000;
		public static $IMAGE_NOT_READABLE = 1001;
		public static $IMAGE_NOT_WRITABLE = 1002;
		public static $IMAGE_DESTINATION_NOT_READABLE = 1003;
		public static $IMAGE_DESTINATION_NOT_WRITABLE = 1004;
		public static $IMAGE_TYPE_NOT_SUPPORTED = 1005;
		public static $IMAGE_CREATION_FAILED = 1006;
		public static $IMAGE_WRITE_FAILED = 1007;

		public function ImageException($message, $code = 999) {
			parent::__construct($message, $code);
		}

	}

?>