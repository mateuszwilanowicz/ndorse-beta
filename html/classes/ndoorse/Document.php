<?php
	/**
	 * Generic document class for uploaded files
	 * Currently only used for CVs in the Job functionality, but designed to also be used elsewhere
	 * @author alanh
	 *
	 */
	class Ndoorse_Document extends Model {

		protected $documentID;
		protected $userID;			// document owner

		protected $name;			// "friendly" name for document
		protected $filePath;		// location of file on system
		protected $type;			// document type (e.g. CV)
		protected $dateUploaded;

		protected $status;			// see below

		const STATUS_ACTIVE = 1;
		const STATUS_INACTIVE = 0;

		// file upload validation - should be a comma separated list (no spaces)
		// constant format VALID_[type]_EXTENSIONS
		const VALID_CV_EXTENSIONS = 'doc,docx,rtf,txt,pdf,odt';
        const VALID_IMAGE_EXTENSIONS = 'jpeg,jpg,gif,png';

		/**
		 * Returns array of Documents (optionally of a specified type) belonging to current user
		 * @param String $type
		 * @return array:Ndoorse_Document
		 */
		public static function getDocuments($type = '', $userID = null) {

			$dbConn = DatabaseConnection::getConnection();

			$sql = 'SELECT *
						FROM ndoorse_document
						WHERE userID = :userID
							AND status = ' . Ndoorse_Document::STATUS_ACTIVE;
			if(!empty($type)) {
				$sql .= ' AND type = :type';
			}
			$sql .= ' ORDER BY dateUploaded DESC';

			$stmt = $dbConn->prepareStatement($sql);

			if(is_null($userID)) {
				$stmt->bindParameter('userID', $_SESSION['user']->getID());
			} else {
				$stmt->bindParameter('userID', $userID);
			}

			if(!empty($type)) {
				$stmt->bindParameter('type', $type);
			}

			$result = $stmt->execute();

			$output = array();
			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					$output[] = new Ndoorse_Document($row);
				}
			}
			return $output;

		}

		/**
		 * Returns user-readable max upload size value
		 * @return string
		 */
		public static function getUploadLimit() {

			$max_upload = (int)(ini_get('upload_max_filesize'));
			$max_post = (int)(ini_get('post_max_size'));
			$memory_limit = (int)(ini_get('memory_limit'));
			return min($max_upload, $max_post, $memory_limit) . ' MB';

		}

		/**
		 * Handles file upload, creates Document element and saves
		 * @param String $type type of file
		 * @param String $name file name/description
		 * @return multitype:boolean |multitype:boolean string
		 */
		public static function upload($type, $name = '') {
			$hasFile = false;
			if(empty($name)) {
				$name = 'File uploaded ' . date('d/m/Y');
			}

			if(isset($_FILES[$type]) && $_FILES[$type]['name'] != '') {

				switch($_FILES[$type]['error']) {
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$error = 'The file was too large. Maximum file size is ' . self::getUploadLimit();
						break;
					case UPLOAD_ERR_PARTIAL:
					case UPLOAD_ERR_CANT_WRITE:
					case UPLOAD_ERR_EXTENSION:
						Logger::log('Ndoorse/Document/Upload: Problem uploading file: ' . $_FILES[$type]['error'], 'error');
						$error = 'There was a problem uploading the file. Please try again.';
						break;
					case UPLOAD_ERR_OK:
						$hasFile = true;

				}
			}

			if($hasFile) {

				$oldFile = $_FILES[$type]['name'];
				$oldFileParts = explode('.', $oldFile);
				$ext = end($oldFileParts);

				switch($type) {
					case 'cv':
						$validExts = explode(',', self::VALID_CV_EXTENSIONS);
						break;
                    case 'logo':
                    case 'avatar':
                        $validExts = explode(',', self::VALID_IMAGE_EXTENSIONS);
                        break;

					default:
						$validExts = array();
				}


				if(!empty($validExts) &&  !in_array($ext, $validExts)) {
					$error = 'The file you uploaded is not in a valid format.';
				}

				$ext = '.' . $ext;
				$base = 'resources/' . $type . '/';

				if(!isset($error) && !file_exists(ROOT_PATH . $base)) {
					mkdir(ROOT_PATH . $base, 0664);
				}

				$filename = $base . $_SESSION['user']->getID() . '_' . date('YmdHis') . $ext;
				if(!isset($error) && move_uploaded_file($_FILES[$type]['tmp_name'], ROOT_PATH . $filename)) {
					chmod(ROOT_PATH . $filename, 0664);
				}

				// resize image if necessary
				if($type == 'avatar') {
					Image::resize(ROOT_PATH . $filename, ROOT_PATH . $filename, 244, 244, false);
				}

				$document = new Ndoorse_Document();

				$document->userID = $_SESSION['user']->getID();
				$document->filePath = $filename;
				$document->name = $name;
				$document->type = $type;
				$document->status = self::STATUS_ACTIVE;

				if(!isset($error) && $document->saveModel()) {
					return array(true, $document->getID());
				}
				$error = 'Could not upload this file.';
			}

			if(!isset($error)) {
				$error = '';
			}
			return array(false, $error);
		}

	}
?>