<?php 
	include_once('model.php');
	class Staff extends Model{

        protected $staff_id;
		public $first_name;
		public $last_name;
		public $sex;
		public $phone;
		public $email;
		public $address;
		public $status;
		public $password;
		public $lga;
		public $month;
		public $created_at;
		public $modified_at;

		public $passport;
		public $error = array();

		public static $class_name = 'Staff';
		public static $table_name = 'staff';
		public static $primary_key = 'staff_id';
		public static $table_fields = array('passport','staff_id','first_name','middle_name','last_name','sex','dob','phone','email','address','doe','status','password','created_at','modified_at');
		function __construct() 
		{
			parent::__construct();
		}
		public function getStaffId(){
			return $this->staff_id;      
		}

		public function setStaffId($staff_id){
			return $this->staff_id = $staff_id;
		}
		/**public static function findBySql($sql){
		$obj = new static();
		$objects = $obj->connection->query($sql)->fetchALL(PDO::FETCH_CLASS,static::$class_name);
		return ($objects) ? $objects : false;
	}*/

		public static function authenticate($password, $staff_id){
			$password = md5($password);
			$sql = "SELECT *FROM ".static::$table_name." WHERE staff_id = '$staff_id' AND password = '$password' LIMIT 1";
			$staff = static::findBySql($sql);
			return ($staff) ? array_shift($staff) : false;
		}
		public function setNewStaffId(){
			if($lastStaff = static::last()){
				$lastId= explode('/', $lastStaff->staff_id);
				if (strlen(++$lastId[1])<3){
					$this->staff_id = 'staff/' .str_repeat('0', 3-strlen($lastId[1])).$lastId[1];
				}else{
					$this->staff_id = 'staff/' .$lastId[1];
				}
			} else{
				$this->staff_id = 'staff/001';
			}
		}

		public function insertStaff(){
			$this->setNewStaffId();
			$this->password = md5($this->password);
			return ($this->create())? true:false;
		}
			
		protected $upload_errors = array (
			UPLOAD_ERR_OK      			=>  "No errors.",
			UPLOAD_ERR_INI_SIZE			=>	"Larger than upload_max_filesize.",
			UPLOAD_ERR_FORM_SIZE        =>  "Larger than form MAX_file_size",
			UPLOAD_ERR_PARTIAL			=>  "Partial upload",
			UPLOAD_ERR_NO_FILE          =>  "No file.",
			UPLOAD_ERR_NO_TMP_DIR       =>  "No temporary directory",
			UPLOAD_ERR_CANT_WRITE       =>  "Cant write to disk.",
			UPLOAD_ERR_EXTENSION        =>  "File upload stopped by extension"  	
		);

		public  function attach_file($file){
			if(!$file || empty($file) || !is_array($file)){

				$this->errors[] = "no file was uploaded";
				return false;}
				elseif ($file['error'] !=0) {
					$this->errors[] = $this->upload_errors[$file['error']];
				    return false;

				}
				
			 else{
				if(!isset($this->staff_id))
					$this->setNewStaffId();

				$this->temp_path = $file['tmp_name'];
				$this->passport  = str_replace("/","_", $this->staff_id).".".basename($file["type"]);
				$this->type = $file['type'];
				$this->size = $file['size'];

				return true;
			}
		}

		public function save_with_file(){
			if(!empty($this->errors)){
				return false;
			}
			if (empty($this->passport) || empty ($this->temp_path)) {
				$this->errors[] = "the file location was not available.";
				return false;
			}
			$target_path = "img/staff/" .$this->passport;

			if(move_uploaded_file($this->temp_path, $target_path)){
				if(static::find($this->staff_id)){
					$this->update();
				}else{
					$this->password = md5($this->password);
					$this->create();
				}
				unset($this->temp_path);
				return true;
			}else{
				$this->errors[] = "The file upload failed, possible due to incorrect permisions on the upload folder";
				return false;
			}
		}
	}
?>