<?php

require_once( "mysql.class.php" );

class Language{

        private $id_language = -1;
        private $shortname_language;
	private $longname_language;
        private $msg = "";
        private $db = null;

        public function Language(){
                $this->db = new MySQL();
        }

        public function getMsg(){
                return $this->msg;
        }

        public function getId(){
                return $this->id_language;
        }

        public function getLanguages(){
                $langs = array();
                $query = "SELECT lang.id_lang, lang.shortname_lang, lang.longname_lang FROM lang";
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `lang` -- Select command";
                }
                if( $this->db->RowCount() > 0 ){
                        $this->db->MoveFirst();
                        while( !$this->db->EndOfSeek() ){
                                $row = $this->db->Row();
                                $langs[] = array( 'id' => $row->id_lang, 'shortname' => $row->shortname_lang, 'longname' => $row->longname_lang );
                        }
                }
                return json_encode( $langs, JSON_FORCE_OBJECT );
        }

	public function getFlags( $idLang ){
		$flags = array();
                $query = "SELECT flag.id_flag, flag.value_flag, pos.label_pos, flag.id_pos, flag.id_lang FROM flag INNER JOIN pos ON flag.id_pos = pos.id_pos WHERE flag.id_lang = " . $idLang;
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `flag` -- Select command";
                }
                if( $this->db->RowCount() > 0 ){
                        $this->db->MoveFirst();
                        while( !$this->db->EndOfSeek() ){
                                $row = $this->db->Row();
                                $flags[] = array( 'id' => $row->id_flag, 'value' => $row->value_flag, 'id_pos' => $row->id_pos, 'label_pos' => $row->label_pos );
                        }
                }
                return json_encode( $flags, JSON_FORCE_OBJECT );
	}

	public function getSpecificTypes( $idLang ){
		$types = array();
                $query = "SELECT specific_type.id_specific_type, specific_type.value_specific_type, pos.label_pos, specific_type.id_pos, specific_type.id_lang FROM specific_type INNER JOIN pos ON specific_type.id_pos = pos.id_pos WHERE specific_type.id_lang = " . $idLang;
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `specific types` -- Select command";
                }
                if( $this->db->RowCount() > 0 ){
                        $this->db->MoveFirst();
                        while( !$this->db->EndOfSeek() ){
                                $row = $this->db->Row();
                                $types[] = array( 'id' => $row->id_specific_type, 'value' => $row->value_specific_type, 'id_pos' => $row->id_pos, 'label_pos' => $row->label_pos );
                        }
                }
                return json_encode( $types, JSON_FORCE_OBJECT );
	}

        public function addLanguage( $longname, $shortname ){
                $this->shortname_language = $shortname;
                $this->longname_language = $longname;
        }

	public function registerLanguage(){
                $query = "SELECT * FROM lang WHERE shortname_lang = '" . $this->shortname_language . "' OR longname_lang = '" . $this->longname_language . "' LIMIT 1";
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `user` -- Select command";
                }
                if( $this->db->RowCount() > 0 ){
                        $this->msg = "ERROR: Language already registered.";
                        return 0;
                }
                $query = "INSERT INTO lang ( shortname_lang, longname_lang ) VALUES ('" . $this->shortname_language . "', '" . $this->longname_language . "')";
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `lang` -- Insert command";
                }
                $this->id_language = $this->db->GetLastInsertID(); 
                return 1;
        }

	public function updateLanguage( $idLang, $longname, $shortname ){
		$query = "UPDATE lang SET shortname_lang = '$shortname', longname_lang = '$longname' WHERE id_lang = $idLang";
		if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `lang` -- Update command";
                        return 0;
                }
                return 1;
	}

        public function deleteLanguage( $idLang ){
                $query = "DELETE FROM lang WHERE lang.id_lang = " . $idLang;
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `lang` -- Delete command";
                        return 0;
                }
		$query = "DELETE FROM flag WHERE flag.id_lang = " . $idLang;
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `flag` -- Delete command";
                        return 0;
                }
		$query = "DELETE FROM specific_type WHERE specific_type.id_lang = " . $idLang;
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `specific_type` -- Delete command";
                        return 0;
                }
                return 1;
        }

	public function registerSpecificTypes( $idLang, $noun, $adj, $verb ){
		foreach( $noun as $n ){
			$this->manageSpecificType( $idLang, $n, '1' );
		}
		foreach( $adj as $a ){
			$this->manageSpecificType( $idLang, $a, '2' );
		}
		foreach( $verb as $v ){
			$this->manageSpecificType( $idLang, $v, '3' );
		}
	}

        public function registerFlags( $idLang, $noun, $adj, $verb ){
                foreach( $noun as $n ){
                        $this->manageFlag( $idLang, $n, '1' );
                }
                foreach( $adj as $a ){
                        $this->manageFlag( $idLang, $a, '2' );
                }
                foreach( $verb as $v ){
                        $this->manageFlag( $idLang, $v, '3' );
                }
        }

	public function deleteSpecificType( $idSpecific ){
		$query = "DELETE FROM specific_type WHERE id_specific_type = " . $idSpecific;
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `specific_type` -- Delete delete";
                        return 0;
                }
                return 1;
	}

	public function deleteFlag( $idFlag ){
                $query = "DELETE FROM flag WHERE id_flag = " . $idFlag;
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `flag` -- Delete delete";
                        return 0;
                }
                return 1;
        }

	private function manageSpecificType( $idLang, $couple, $idPos ){
		$query = 'SELECT * FROM specific_type WHERE value_specific_type = "' . $couple[ 1 ] . '" AND id_pos = ' . $idPos . ' AND id_lang = ' . $idLang . ' LIMIT 1';
		if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
       	                $this->msg = "Database error -- table `specific_type` -- Select command";
                }
       	        if( $this->db->RowCount() > 0 ){
			$tmpRes = $this->db->Row();
			$query = 'UPDATE specific_type SET value_specific_type = "' . $couple[ 1 ] . '", id_pos = ' . $idPos . ', id_lang = ' . $idLang . ' WHERE id_specific_type = ' . $tmpRes->id_specific_type;
			if( !$this->db->Query( $query ) ){
		                $this->db->Kill();
				return -1;
			}
		}else{
			$query = 'INSERT INTO specific_type ( value_specific_type, id_pos, id_lang ) VALUES ( "' . $couple[ 1 ] . '", ' . $idPos . ', ' . $idLang . ' )';
                        if( !$this->db->Query( $query ) ){
                                $this->db->Kill();
                                $this->msg = "Database error -- table `specific_type` -- Insert command";
				return -1;
                        }
		}
		return 1;
	}

	private function manageFlag( $idLang, $couple, $idPos ){
                $query = 'SELECT * FROM flag WHERE value_flag = "' . $couple[ 1 ] . '" AND id_pos = ' . $idPos . ' AND id_lang = ' . $idLang . ' LIMIT 1';
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        $this->msg = "Database error -- table `flag` -- Select command";
                }
                if( $this->db->RowCount() > 0 ){
                        $tmpRes = $this->db->Row();
                        $query = 'UPDATE flag SET value_flag = "' . $couple[ 1 ] . '", id_pos = ' . $idPos . ', id_lang = ' . $idLang . ' WHERE id_flag = ' . $tmpRes->id_flag;
                        if( !$this->db->Query( $query ) ){
                                $this->db->Kill();
                                return -1;
                        }
                }else{
                        $query = 'INSERT INTO flag ( value_flag, id_pos, id_lang ) VALUES ( "' . $couple[ 1 ] . '", ' . $idPos . ', ' . $idLang . ' )';
                        if( !$this->db->Query( $query ) ){
                                $this->db->Kill();
                                $this->msg = "Database error -- table `flag` -- Insert command";
                                return -1;
                        }
                }
                return 1;
        }

}

if( isset( $_POST[ 'longname' ] ) && isset( $_POST[ 'shortname' ] ) && !empty( $_POST[ 'longname' ] ) && !empty( $_POST[ 'shortname' ] ) ){
	$lang = new Language();
	$flag = [ 1 => [], 2 => [], 3 => [] ];
	$specific = [ 1 => [], 2 => [], 3 => [] ];
	if( isset( $_POST[ 'specific_types' ] ) ){
		$specific = json_decode( $_POST[ 'specific_types' ], true );
	}
	if( isset( $_POST[ 'flags' ] ) ){
                $flag = json_decode( $_POST[ 'flags' ], true );
        }
	if( isset( $_POST[ 'update' ] ) && $_POST[ 'update' ] != '-1' ){
		$res = $lang->updateLanguage( $_POST[ 'update' ], $_POST[ 'longname' ], $_POST[ 'shortname' ] );
		$lang->registerSpecificTypes( $_POST[ 'update' ], $specific[ 1 ], $specific[ 2 ], $specific[ 3 ] );
		$lang->registerFlags( $_POST[ 'update' ], $flag[ 1 ], $flag[ 2 ], $flag[ 3 ] );
		echo "Language updated successfully";
	}else{
		$lang->addLanguage( $_POST[ 'longname' ], $_POST[ 'shortname' ] );
		$res = $lang->registerLanguage();
		if( $lang->getId() != -1 && $res == 1 ){
			$lang->registerSpecificTypes( $lang->getId(), $specific[ 1 ], $specific[ 2 ], $specific[ 3 ] );
			$lang->registerFlags( $lang->getId(), $flag[ 1 ], $flag[ 2 ], $flag[ 3 ] );
		        echo "Language added successfully";
		}else{
        		echo $lang->getMsg();
	        }
	}
}

if( isset( $_GET[ "list" ] ) && $_GET[ "list" ] == "all" ){
        $lang = new Language();
        echo $lang->getLanguages();
}

if( isset( $_GET[ "flags" ] ) && $_GET[ "flags" ] != "-1" ){
        $lang = new Language();
        echo $lang->getFlags( $_GET[ "flags" ] );
}

if( isset( $_GET[ "specific_types" ] ) && $_GET[ "specific_types" ] != "-1" ){
        $lang = new Language();
        echo $lang->getSpecificTypes( $_GET[ "specific_types" ] );
}

if( isset( $_GET[ "delete" ] ) && $_GET[ "delete" ] != "-1" ){
        $lang = new Language();
        $lang->deleteLanguage( $_GET[ "delete" ] );
}

if( isset( $_GET[ "delete_flag" ] ) && $_GET[ "delete_flag" ] != "-1" ){
        $lang = new Language();
        $lang->deleteFlag( $_GET[ "delete_flag" ] );
}

if( isset( $_GET[ "delete_feature" ] ) && $_GET[ "delete_feature" ] != "-1" ){
        $lang = new Language();
        $lang->deleteSpecificType( $_GET[ "delete_feature" ] );
}

