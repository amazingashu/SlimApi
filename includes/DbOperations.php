<?php 

    class DbOperations{

        private $con; 

        function __construct(){
            require_once dirname(__FILE__) . '/DbConnect.php';
            $db = new DbConnect; 
            $this->con = $db->connect(); 
        }

     
 public function createUser($Name, $Email, $Phone, $Password){
           if(!$this->isEmailExist($Email)){
			   
			  /* if (function_exists('date_default_timezone_set')) {
    				date_default_timezone_set('Asia/Kolkata');
						}
					$CreatedDate = date('d-m-Y h:i:s');
*/
					
					
                $stmt = $this->con->prepare("INSERT INTO tblUsers (Name, Email, Phone, Password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $Name, $Email, $Phone, $Password);
                if($stmt->execute()){
                    return USER_CREATED; 
                }else{
                    return USER_FAILURE;
                }
           }
           return USER_EXISTS; 
        }
        public function userLogin($Email, $Password){
            if($this->isEmailExist($Email)){
                $hashed_password = $this->getUsersPasswordByEmail($Email); 
                if(password_verify($Password, $hashed_password)){
                    return USER_AUTHENTICATED;
                }else{
                    return USER_PASSWORD_DO_NOT_MATCH; 
                }
            }else{
                return USER_NOT_FOUND; 
            }
        }

        private function getUsersPasswordByEmail($Email){
            $stmt = $this->con->prepare("SELECT Password FROM tblUsers WHERE Email = ?");
            $stmt->bind_param("s", $Email);
            $stmt->execute(); 
            $stmt->bind_result($password);
            $stmt->fetch(); 
            return $password; 
        }

        public function getAllUsers(){
            $stmt = $this->con->prepare("SELECT * FROM tblUsers;");
            $stmt->execute(); 
            $stmt->bind_result($UserId, $Email, $Name, $Phone);
            $users = array(); 
            while($stmt->fetch()){ 
                $user = array(); 
                $user['UserId'] = $UserId; 
                $user['Email']=$Email; 
                $user['Name'] = $Name; 
                $user['Phone'] = $Phone; 
                array_push($users, $user);
            }             
            return $users; 
        }

        public function getUserByEmail($Email){
            $stmt = $this->con->prepare("SELECT * FROM tblUsers WHERE Email = ?");
            $stmt->bind_param("s", $Email);
            $stmt->execute(); 
            $stmt->bind_result($UserId, $Email, $Name, $Phone);
            $stmt->fetch(); 
            $user = array(); 
             $user['UserId'] = $UserId; 
                $user['Email']=$Email; 
                $user['Name'] = $Name; 
                $user['Phone'] = $Phone; 
            return $user; 
        }

        public function updateUser($Email, $Name, $Phone, $id){
            $stmt = $this->con->prepare("UPDATE tblUsers SET Email = ?, Name = ?, Phone = ? WHERE UserId = ?");
            $stmt->bind_param("sssi", $Email, $Name, $Phone, $UserId);
            if($stmt->execute())
                return true; 
            return false; 
        }

        public function updatePassword($currentpassword, $newpassword, $Email){
            $hashed_password = $this->getUsersPasswordByEmail($email);
            
            if(password_verify($currentpassword, $hashed_password)){
                
                $hash_password = password_hash($newpassword, PASSWORD_DEFAULT);
                $stmt = $this->con->prepare("UPDATE tblUsers SET Password = ? WHERE email = ?");
                $stmt->bind_param("ss",$hash_password, $Email);

                if($stmt->execute())
                    return PASSWORD_CHANGED;
                return PASSWORD_NOT_CHANGED;

            }else{
                return PASSWORD_DO_NOT_MATCH; 
            }
        }

        public function deleteUser($UserId){
            $stmt = $this->con->prepare("DELETE FROM tblUsers WHERE UserId = ?");
            $stmt->bind_param("i", $UserId);
            if($stmt->execute())
                return true; 
            return false; 
        }

        private function isEmailExist($Email){
            $stmt = $this->con->prepare("SELECT UserId FROM tblUsers WHERE email = ?");
            $stmt->bind_param("s", $Email);
            $stmt->execute(); 
            $stmt->store_result(); 
            return $stmt->num_rows > 0;  
        }
    }
