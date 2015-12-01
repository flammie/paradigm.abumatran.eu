<?php

require_once( "mysql.class.php" );

class User{

	private $id_user = -1;
	private $name_user;
	private	$pwd_user;
	private $email_user;
	private $lang = array();
	private $msg = "";
	private $db = null;

	public function User(){
		$this->db = new MySQL();
	}

	public function addUser( $name, $pwd, $email = "", $lang = array() ){
                $this->name_user = $name;
                $this->pwd_user = md5( $pwd );
                $this->email_user = $email;
		if( count( $lang ) > 0 ){
			foreach( $lang as $l ){
				$this->lang[] = $l;
			}
		}
        }

	public function updateUser( $idUser, $name = "", $pwd = "", $email = NULL, $lang = array() ){
		$this->setIdUser( $idUser );
		if( $name != '' ){
			$this->setName( $name );
			$this->updateUserName();
		}
		if( $pwd != '' ){
			$this->setPwd( $pwd );
                        $this->updateUserPwd();
		}
                $this->setEmail( $email );
                $this->updateUserEmail();
		if( count( $lang ) > 0 ){
                        foreach( $lang as $l ){
                                $this->lang[] = $l;
                        }
			$this->updateUserLangs();
                }
	}

	private function setIdUSer( $idUser ){
		$this->id_user = $idUser;
	}

	public function setName( $name ){
		$this->name_user = $name;
	}

	private function setPwd( $pwd ){
		$this->pwd_user = md5( $pwd );
	}

	public function setEmail( $email ){
		$this->email_user = $email;
	}

	public function getId(){
		return $this->id_user;
	}

	public function getName(){
		return $this->name_user;
	}

	public function getEmail(){
		return $this->email_user;
	}

	public function getMsg(){
		return $this->msg;
	}

	public function updateUserName(){
		$query = 'UPDATE user set name_user = "' . $this->name_user . '" WHERE id_user = ' . $this->id_user;
		if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `user` -- Update user name";
                }
		return 1;
	}

        public function updateUserPwd(){
                $query = 'UPDATE user set pwd_user = "' . $this->pwd_user . '" WHERE id_user = ' . $this->id_user;
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `user` -- Update user pwd";
                }
		return 1;
        }

        public function updateUserEmail(){
		if( $this->email_user == "" ){ $this->email_user = NULL; }
                $query = 'UPDATE user set email_user = "' . $this->email_user . '" WHERE id_user = ' . $this->id_user;
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `user` -- Update user email";
                }
                return 1;
        }

        public function updateUserLangs(){
                $query = 'DELETE FROM speak WHERE id_user = ' . $this->id_user;
		$this->db->Query( $query );
		foreach( $this->lang as $langid ){
			$query = "INSERT INTO speak ( id_user, id_lang ) VALUES ( " . $this->id_user . ", " . $langid . " )";
	                if( !$this->db->Query( $query ) ){
        	                $this->db->Kill();
                	        $this->msg = "Database error -- table `user` -- Update user lang";
	                }
		}
                return 1;
        }

	public function registerUser(){
		$query = "SELECT * FROM user WHERE name_user = '" . $this->name_user . "' LIMIT 1";
		if( !$this->db->Query( $query ) ){
	                $this->db->Kill();
			$this->msg = "Database error -- table `user` -- Select command";
                }
                if( $this->db->RowCount() > 0 ){
			$this->msg = "ERROR: User name already registered.";
			return 0;
		}
		$query = "INSERT INTO user ( name_user, pwd_user, email_user ) Values ('" . $this->name_user . "', '" . $this->pwd_user . "', '" . $this->email_user . "')";
		if( !$this->db->Query( $query ) ){
			$this->db->Kill();
			$this->msg = "Database error -- table `user` -- Insert command";
		}
		$this->id_user = $this->db->GetLastInsertID(); 
		foreach( $this->lang as $langid ){
			$query = "INSERT INTO speak ( id_user, id_lang ) VALUES ( " . $this->id_user . ", " . $langid . " )";
			if( !$this->db->Query( $query ) ){
	                        $this->db->Kill();
				$this->msg = "Database error -- table `speak` -- Insert command";
        		}
		}
		return 1;
	}

	public function deleteUser( $idUser ){
		$query = "DELETE FROM user WHERE user.id_user = " . $idUser;
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `user` -- Update command";
                        return 0;
                }
                return 1;
	}

	public function activateUser( $idUser ){
		$query = "UPDATE user SET user.activate_user = !user.activate_user WHERE user.id_user = " . $idUser;
		if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
        	        $this->msg = "Database error -- table `user` -- Update command";
			return 0;
                }
		return 1;
	}

	public function getUsers(){
		$users = array();
		$query = "SELECT id_user, name_user, email_user, activate_user FROM user";
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `user` -- Select command";
                }
                if( $this->db->RowCount() > 0 ){
			$this->db->MoveFirst();
			while( !$this->db->EndOfSeek() ){
	                        $row = $this->db->Row();
				$users[] = array( 'id' => $row->id_user, 'name' => $row->name_user, 'email' => $row->email_user, 'activate_user' => $row->activate_user );
			}
                }
		return json_encode( $users, JSON_FORCE_OBJECT );
	}

	public function getAllCounts(){
		return array(
			"Accounts" => $this->getCountAccounts(),
			"Activated accounts" => $this->getCountActivated(),
			"Active users" => $this->getCountActiveUsers(),
		);
	}

	private function getCountAccounts(){
		$query = "SELECT COUNT( DISTINCT user.id_user ) AS cu FROM user";
		if( !$this->db->Query( $query ) ){
                      $this->db->Kill();
                      return -1;
                }
                $this->db->MoveFirst();
                $row = $this->db->Row();
                return $row->cu;
	}

        private function getCountActivated(){
                $query = "SELECT COUNT( DISTINCT user.id_user ) AS cu FROM user WHERE activate_user = 1";
                if( !$this->db->Query( $query ) ){
                      $this->db->Kill();
                      return -1;
                }
                $this->db->MoveFirst();
                $row = $this->db->Row();
                return $row->cu;
        }

        private function getCountActiveUsers(){
                $query = "SELECT COUNT( DISTINCT user.id_user ) AS cu FROM user
			INNER JOIN user_surface_done ON user_surface_done.id_user = user.id_user
			WHERE user.activate_user = 1";
                if( !$this->db->Query( $query ) ){
                      $this->db->Kill();
                      return -1;
                }
                $this->db->MoveFirst();
                $row = $this->db->Row();
                return $row->cu;
        }

	public function getOnlineUsers(){
		$toreturn = array();
		$query = "SELECT user.id_user, user.name_user FROM user WHERE user.user_online = 1";
                if( !$this->db->Query( $query ) ){
                      $this->db->Kill();
                      return -1;
                }
                $this->db->MoveFirst();
		while( !$this->db->EndOfSeek() ){
			$toreturn[] = $this->db->Row();
		}
                return $toreturn;

	}

	public function getUserLanguages( $userId ){
		$toreturn = array();
                $query = "SELECT speak.id_lang from speak where speak.id_user = " . $userId;
                $res = $this->db->QueryArray( $query, MYSQLI_NUM );
                if( $res === false ){
                      return -1;
                }
		return $res;
	}
}

if( isset( $_POST ) && isset( $_POST[ "name" ] ) && isset( $_POST[ 'update' ] ) && $_POST[ 'update' ] != '' ){
	$user = new User();
        $user->updateUser( $_POST[ 'update' ], $_POST[ "name" ], $_POST[ "pwd" ], $_POST[ "email" ], $_POST[ "lang" ] );
	if( $user->getId() != -1 ){
        	echo "User updated successfully";
	}else{
		echo $user->getMsg();
        }
}elseif( isset( $_POST ) && isset( $_POST[ "name" ] ) && isset( $_POST[ "pwd" ] ) ){
	if( !empty( $_POST[ "name" ] ) && !empty( $_POST[ "pwd" ] ) ){
		$lang = array();
		if( isset( $_POST[ "lang" ] ) && !empty( $_POST[ "lang" ] ) && is_array( $_POST[ 'lang' ] ) ){
			$lang = $_POST[ "lang" ];
		}
		$user = new User();
		$user->addUser( $_POST[ "name" ], $_POST[ "pwd" ], $_POST[ "email" ], $lang );
		$registration = $user->registerUser();
		if( $user->getId() != -1 && $registration == 1 ){
			echo "User registered successfully";
		}else{
			echo $user->getMsg();
		}
	}
}

if( isset( $_GET[ "list" ] ) && $_GET[ "list" ] == "all" ){
	$user = new User();
	echo $user->getUsers();
}

if( isset( $_POST[ "overview" ] ) && $_POST[ "overview" ] == "all" ){
	$user = new User();
	echo json_encode( $user->getAllCounts(), JSON_FORCE_OBJECT );
}

if( isset( $_POST[ "overview" ] ) && $_POST[ "overview" ] == "users" ){
	$user = new User();
	echo json_encode( $user->getOnlineUsers(), JSON_FORCE_OBJECT );
}

if( isset( $_GET[ "activate" ] ) && !empty( $_GET[ "activate" ] ) ){
	$user = new User();
	$user->activateUser( $_GET[ "activate" ] );
}

if( isset( $_GET[ "delete" ] ) && !empty( $_GET[ "delete" ] ) ){
        $user = new User();
        $user->deleteUser( $_GET[ "delete" ] );
}

if( isset( $_POST[ "getlang" ] ) && $_POST[ "getlang" ] != "" ){
        $user = new User();
        echo json_encode( $user->getUserLanguages( $_POST[ "getlang" ] ), JSON_FORCE_OBJECT );
}

?> 
