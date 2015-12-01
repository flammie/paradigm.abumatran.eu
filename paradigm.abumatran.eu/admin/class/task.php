<?php
require_once( "mysql.class.php" );
ini_set("memory_limit","512M");

class Task{

	private $taskUtils = null;
	private $surfaceForms = array();
	private $lemma = array();
	private $paradigm = array();
	private $probability = array();
	private $expanded = array();
	private $msg_err = "";	

	public $newSurfaceForms = 0;
	public $duplicateSurfaceForms = 0;	

	public function Task( &$taskUtils ){
		$this->taskUtils = $taskUtils;
		foreach( $this->taskUtils->getUploadedData() as $key => $val ){
			$this->pushData( $key, $val );
		}
		$this->checkData();
		$this->insertData();
        }

	public function getSurfaceForms(){
		return $this->surfaceForms;
	}

	public function getLemma(){
		return $this->lemma;
	}

	public function getParadigm(){
		return $this->paradigm;
	}

	public function getProbability(){
		return $this->probability;
	}

	public function getExpanded(){
		return $this->expanded;
	}

	private function checkData(){
		if( count( $this->surfaceForms ) == 0 || count( $this->surfaceForms ) != count( $this->lemma ) || count( $this->lemma ) != count( $this->paradigm ) || count( $this->lemma ) != count( $this->expanded ) || count( $this->lemma ) != count( $this->probability ) ){
			return 0;
		}
		return 1;
	}

	private function pushData( $key, $val ){
		if( (string) $key == 'surface_form' ){
			$this->surfaceForms[] = $val;
		}
		if( (string) $key == 'candidates' ){
			$this->lemma[] = array();
			$this->probability[] = array();
			$this->paradigm[] = array();
			$this->expanded[] = array();
		}
		if( (string) $key == 'lemma' ){
			$this->lemma[ count( $this->lemma ) - 1 ][] = $val;
		}
		if( (string) $key == 'paradigm' ){
			$this->paradigm[ count( $this->paradigm ) - 1 ][] = $val;
		}
		if( (string) $key == 'probability' ){
			$tmp = (float) $val;
			$this->probability[ count( $this->probability ) - 1 ][] = round( $tmp, 6 );
		}
		if( (string) $key == 'expanded' ){
			$tmp = "";
			foreach( $val as $couple ){
	                        $tmp = $tmp . '::' .  $couple[ 0 ] . '__' . $couple[ 1 ];
			}
			$tmp = ltrim( $tmp, '::' );
			$this->expanded[ count( $this->expanded ) - 1 ][] = $tmp;
                }
	}

        private function insertTask( &$db ){
                $query = "INSERT INTO task ( date_create, activate_task ) VALUES ( '" . date( "YmdHis" ) . "', 0 )";
                $res = $this->runQuery( $query, $db );
                return $db->GetLastInsertID();
        }

	private function insertSurface( &$db, $i ){
		$query = "SELECT * FROM surface_form WHERE value_surface_form = '" . $this->surfaceForms[ $i ] . "' LIMIT 1";
		$res = $this->runQuery( $query, $db );
		if( $db->RowCount() == 0 ){
			$this->newSurfaceForms += 1;
			$query = "INSERT INTO surface_form ( value_surface_form ) VALUES ('" . $this->surfaceForms[ $i ] . "' )";
	                $res = $this->runQuery( $query, $db );
        	        return $db->GetLastInsertID();
		}
		$this->duplicateSurfaceForms += 1;
		$db->MoveFirst();
		$row = $db->Row();
		return $row->id_surface_form;
	}

	private function insertLemma( $lemma ){
		return $this->taskUtils->easyInsert( "lemma", "value_lemma", $lemma );
	}

	private function insertParadigm( $paradigm ){
		return $this->taskUtils->easyInsert( "paradigm", "value_paradigm", $paradigm );
	}

	private function insertExpanded( $expanded ){
		return $this->taskUtils->justInsert( "expanded", "value_expanded", $expanded );
        }

	private function insertCandidate( &$db, $index, $surfaceId, $lemmaId, $paradigmId, $expandedId, $proba ){
		$query = "SELECT candidate.id_candidate FROM candidate WHERE candidate.id_surface_form = " . $surfaceId . " AND candidate.id_lemma = " . $lemmaId . " AND candidate.id_paradigm = " . $paradigmId;
		$res = $this->runQuery( $query, $db );
		if( $db->RowCount() == 0 ){
			$query = "INSERT INTO candidate ( id_surface_form, id_lemma, id_paradigm, id_expanded, probability ) VALUES ( " . $surfaceId . ", " . $lemmaId . ", " . $paradigmId . ", " . $expandedId . ", " . $proba . ")";
			$res = $this->runQuery( $query, $db );
			return $db->GetLastInsertID();
		}
		$db->MoveFirst();
		$row = $db->Row();
		return $row->id_candidate;
	}

	private function insertTaskContent( &$db, $lastTaskId, $candidateId ){
		$query = "INSERT INTO task_content ( id_task, id_candidate ) VALUES ( " . $lastTaskId . ", " . $candidateId . " )";
		$res = $this->runQuery( $query, $db );
                return $db->GetLastInsertID();
	}

	private function loopOverCandidates( &$db, $index, $surface_id, $lastTaskId ){
		if( count( $this->expanded[ $index ] ) != count( $this->lemma[ $index ] ) || count( $this->lemma[ $index ] ) != count( $this->paradigm[ $index ] ) || count( $this->lemma[ $index ] ) != count( $this->probability[ $index ] ) ){
			return 0;
		}
		for( $i = 0 ; $i < count( $this->lemma[ $index ] ) ; $i++ ){
			$lemmaId = $this->insertLemma( $this->lemma[ $index ][ $i ] );
        	        $paradigmId = $this->insertParadigm( $this->paradigm[ $index ][ $i ] );
                	$expandedId = $this->insertExpanded( $this->expanded[ $index ][ $i ] );
			$candidateId = $this->insertCandidate( $db, $i, $surface_id, $lemmaId, $paradigmId, $expandedId, $this->probability[ $index ][ $i ] );
			$taskContentId = $this->insertTaskContent( $db, $lastTaskId, $candidateId );
		}
	}

	private function insertData(){
		$db = $this->taskUtils->getDB();
		$lastTaskId = $this->insertTask( $db );
		for( $i = 0 ; $i < count( $this->surfaceForms ) ; $i++ ){
			$lastSurfaceId = $this->insertSurface( $db, $i );
			$this->loopOverCandidates( $db, $i, $lastSurfaceId, $lastTaskId );
		}
	}

	private function runQuery( $query, &$db ){
		if( !$db->Query( $query ) ){
                	$db->Kill();
                        echo "Database error";
                        exit();
                }
		return 1;
	}
}

class TaskUtils{
	
	private $db = null;
	private $taskDate = null;
	private $uploadedData = null;

	public function TaskUtils(){
		$this->db = new MySQL();
		$this->setDBEncoding( $this->db );
		$this->taskDate = date( "YmdHis" );
	}

        private function setDBEncoding( &$db ){
                $query = "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'";
		if( !$db->Query( $query ) ){
                        $db->Kill();
			echo "Error with DB encoding configuration query!";
		}
        }

	public function getDB(){
		return $this->db;
	}

	public function getUploadedData(){
		return $this->uploadedData;
	}

	public function writeUploadedTask( $content ){
		$handler = fopen( "../task_up/" . $this->taskDate . ".json", "w" );
		fwrite( $handler, json_encode( $content ) );
		fclose( $handler );
	}

	public function parseData( $uploaded_data ){
                $this->uploadedData = new RecursiveIteratorIterator( new RecursiveArrayIterator( $uploaded_data ), RecursiveIteratorIterator::SELF_FIRST );
        }

        public function easyInsert( $tableName, $fieldName, $value ){
                $query = "SELECT id_" . $tableName . " AS id FROM " . $tableName . " WHERE ". $fieldName . " = '" . $value . "'";
		if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        return -1;
                }
                if( $this->db->RowCount() == 0 ){
			return $this->justInsert( $tableName, $fieldName, $value );
                }else{
                        $this->db->MoveFirst();
                        $row = $this->db->Row();
                        return $row->id;
                }
        }

	public function justInsert( $tableName, $fieldName, $value ){
		$query = "INSERT INTO " . $tableName . " ( ". $fieldName . " ) VALUES ( '" . $value . "' )";
                if( !$this->db->Query( $query ) ){
	              $this->db->Kill();
                      return -1;
        	}
                return $this->db->GetLastInsertID();
	}

	public function getTasks(){
		$query = 'SELECT count( surface.id_surface ) as nb_surface, task.id_task, task.date_create, task.id_lang, task.activate_task FROM task LEFT JOIN surface ON surface.id_task = task.id_task GROUP BY task.id_task ORDER BY task.date_create DESC';
		$res = $this->db->QueryArray( $query, MYSQLI_ASSOC );
                if( $res === false ){
                        return -1;
                }
                return $res;
	}

	public function getTaskDetails( $taskId ){
		$flag = 0;
		$done = 0;
		$expanded_lock = 0;
		$query = 'SELECT count( DISTINCT surface.id_surface ) as cc FROM user_surface_done INNER JOIN surface ON user_surface_done.id_surface = surface.id_surface WHERE surface.id_task = ' . $taskId;
		$res = $this->db->QuerySingleRowArray( $query, MYSQLI_ASSOC );
                if( $res !== false ){
			$done = $res[ 'cc' ];
                }
		$query = 'SELECT count( DISTINCT surface.id_surface ) as cc FROM user_surface_flag INNER JOIN surface ON user_surface_flag.id_surface = surface.id_surface WHERE surface.id_task = ' . $taskId;
                $res = $this->db->QuerySingleRowArray( $query, MYSQLI_ASSOC );
                if( $res ){
                        $flag = $res[ 'cc' ];
                }
                $query = 'SELECT count( DISTINCT surface.id_surface ) as cc FROM user_surface_expanded_lock INNER JOIN surface ON user_surface_expanded_lock.id_surface = surface.id_surface WHERE surface.id_task = ' . $taskId;
                $res = $this->db->QuerySingleRowArray( $query, MYSQLI_ASSOC );
                if( $res ){
			$expanded_lock = $res[ 'cc' ];
                }
                return [ 'flag' => $flag, 'expanded_lock' => $expanded_lock, 'done' => $done ];
	}

	public function getAssociatedUsers( $taskId ){
		$query = 'SELECT user.id_user, user.name_user FROM user INNER JOIN user_task ON user_task.id_user = user.id_user WHERE user_task.id_task = ' . $taskId . ' GROUP BY user.id_user ORDER BY user.name_user ASC';
                $res = $this->db->QueryArray( $query, MYSQLI_ASSOC );
                if( $res === false ){
                        return -1;
                }
                return $res;
	}

	public function getNotAssociatedUsers( $taskId ){
		$query = 'SELECT user.id_user, user.name_user FROM user WHERE user.id_user NOT IN ( SELECT user.id_user FROM user INNER JOIN user_task ON user_task.id_user = user.id_user WHERE user_task.id_task = ' . $taskId . ' ) GROUP BY user.id_user  ORDER BY user.name_user ASC';
		$res = $this->db->QueryArray( $query, MYSQLI_ASSOC );
                if( $res === false ){
                        return -1;
                }
                return $res;
	}

	public function associateUser( $taskId, $userId ){
		$query = 'SELECT id_user FROM user_task WHERE id_task = ' . $taskId . ' AND id_user = ' . $userId;
		$res = $this->db->QuerySingleRowArray( $query, MYSQLI_ASSOC );
                if( $res === false ){
			$query = 'INSERT INTO user_task ( id_user, id_task ) VALUES ( ' . $userId . ', ' . $taskId . ' )';
			$res = $this->db->Query( $query );
                }else{
			$query = 'DELETE FROM user_task WHERE id_task = ' . $taskId . ' AND id_user = ' . $userId;
			$res = $this->db->Query( $query );
		}
                return $res;
	}

	private function deleteLemma( $lemmaId ){
		$query = 'DELETE FROM lemma WHERE id_lemma IN ( ' . implode( ',', $lemmaId ) . ' )';
		if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        echo "Error while deleting lemmas";
                }
	}

	private function deleteTaskLemma( $taskId ){
		$lemmaIds = [];
		$query = 'SELECT lemma.id_lemma FROM lemma INNER JOIN candidate ON candidate.id_lemma = lemma.id_lemma INNER JOIN surface ON surface.id_surface = candidate.id_surface WHERE surface.id_task = ' . $taskId;
		$res = $this->db->Query( $query );
		foreach( $res as $key => $val ){
			$lemmaIds[] = $val[ 'id_lemma' ];
		}
		if( count( $lemmaIds ) > 0 ){
			$query = 'DELETE FROM lemma WHERE lemma.id_lemma IN ( ' . implode( ',', $lemmaIds ) . ' )';
			if( !$this->db->Query( $query ) ){
                	        $this->db->Kill();
                        	echo "Error while deleting lemmas";
	                }
		}
	}

        private function deleteParadigm( $paradigmId ){
                $query = 'DELETE FROM paradigm WHERE id_paradigm IN ( ' . implode( ',', $paradigmId ) . ' )';
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        echo "Error while deleting paradigms";
                }
        }

        private function deleteTaskParadigm( $taskId ){
		$paradigmIds = [];
		$query = 'SELECT paradigm.id_paradigm FROM paradigm INNER JOIN candidate ON candidate.id_paradigm = paradigm.id_paradigm INNER JOIN surface ON surface.id_surface = candidate.id_surface WHERE surface.id_task = ' . $taskId;
		$res = $this->db->Query( $query );
                foreach( $res as $key => $val ){
                        $paradigmIds[] = $val[ 'id_paradigm' ];
                }
		if( count( $paradigmIds ) > 0 ){
	                $query = 'DELETE FROM paradigm WHERE id_paradigm IN ( ' . implode( ',', $paradigmIds ) . ' )';
        	        if( !$this->db->Query( $query ) ){
                	        $this->db->Kill();
                        	echo "Error while deleting paradigms";
	                }	
		}
        }

        private function deleteExpanded( $expandedId ){
                $query = 'DELETE FROM expanded WHERE id_expanded IN ( ' . implode( ',', $expandedId ) . ' )';
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        echo "Error while deleting expanded";
                }
        }

        private function deleteTaskExpanded( $taskId ){
		$expandedIds = [];
		$query = 'SELECT expanded.id_expanded FROM expanded INNER JOIN candidate ON candidate.id_expanded = expanded.id_expanded INNER JOIN surface ON surface.id_surface = candidate.id_surface WHERE surface.id_task = ' . $taskId;
		$res = $this->db->Query( $query );
                foreach( $res as $key => $val ){
                        $expandedIds[] = $val[ 'id_expanded' ];
                }
		if( count( $expandedIds ) > 0 ){
	                $query = 'DELETE FROM expanded WHERE id_expanded IN ( ' . implode( ',', $expandedIds ) . ' )';
        	        if( !$this->db->Query( $query ) ){
                	        $this->db->Kill();
	                        echo "Error while deleting expanded";
        	        }
		}
        }

        private function deleteCandidate( $candidateId ){
                $query = 'DELETE FROM candidate WHERE id_candidate IN ( ' . implode( ',', $candidateId ) . ' )';
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        echo "Error while deleting candidates";
                }
        }

        private function deleteTaskCandidate( $taskId ){
		$candidateIds = [];
		$query = 'SELECT candidate.id_candidate FROM candidate INNER JOIN surface ON surface.id_surface = candidate.id_surface WHERE surface.id_task = ' . $taskId;
		$res = $this->db->Query( $query );
                foreach( $res as $key => $val ){
                        $candidateIds[] = $val[ 'id_candidate' ];
                }
		if( count( $candidateIds ) > 0 ){
	                $query = 'DELETE FROM candidate WHERE id_candidate IN ( ' . implode( ',', $candidateIds ) . ' )';
        	        if( !$this->db->Query( $query ) ){
                	        $this->db->Kill();
                        	echo "Error while deleting candidates";
	                }
		}
        }

	private function getCandidateFromSurfaceId( $surfaceId ){
		$candidateId = array();
		$lemmaId = array();
		$paradigmId = array();
		$expandedId = array();
		$query = 'SELECT id_candidate, id_lemma, id_paradigm, id_expanded FROM candidate WHERE id_surface IN ( ' . implode( ',', $surfaceId ) . ' )';
		$res = $this->db->Query( $query, MYSQLI_ASSOC );
		if( $res !== false ){
			foreach( $res as $val ){
				$candidateId[] = $val[ 'id_candidate' ];
				$lemmaId[] = $val[ 'id_lemma' ];
				$paradigmId[] = $val[ 'id_paradigm' ];
				$expandedId[] = $val[ 'id_expanded' ];
			}	
                }
		return [ $candidateId, $lemmaId, $paradigmId, $expandedId ];
	}

	private function getSurfaceFromTaskId( $taskId ){
		$surfaceId = array();
		$query = 'SELECT id_surface FROM surface WHERE id_task = ' . $taskId;
		$res = $this->db->QueryArray( $query, MYSQLI_ASSOC );
		if( $res !== false ){
			foreach( $res as $key => $val ){
				$surfaceId[] = $val[ 'id_surface' ];
			}
		}
		return $surfaceId;
	}

	public function deleteFullTask( $taskId ){
		/*
		task, user_task, surface, candidate, lemma, paradigm, expanded, user_surface_done, user_surface_lock, user_surface_expanded_lock, user_surface_flag
		*/
		$surfaceId = $this->getSurfaceFromTaskId( $taskId );
		#$tmp = $this->getCandidateFromSurfaceId( $surfaceId );
		#$candidateId = $tmp[ 0 ];
		#$lemmaId = $tmp[ 1 ];
		#$paradigmId = $tmp[ 2 ];
		#$expandedId = $tmp[ 3 ];
		#if( count( $lemmaId ) > 0 ){
		$this->deleteTaskLemma( $taskId );
		#}
		#if( count( $paradigmId ) > 0 ){
		$this->deleteTaskParadigm( $taskId );
		#}
		#if( count( $expandedId ) > 0 ){
		$this->deleteTaskExpanded( $taskId );
		#}
		#if( count( $candidateId ) > 0 ){
		$this->deleteTaskCandidate( $taskId );
		#}
		
		if( count( $surfaceId ) > 0 ){
			$this->deleteUserSurfaceDone( $surfaceId );
			$this->deleteUserSurfaceLock( $surfaceId );
			$this->deleteUserSurfaceExpandedLock( $surfaceId );
			$this->deleteUserSurfaceFlag( $surfaceId );
			$this->deleteSurface( $surfaceId );
		}
		if( count( $taskId ) > 0 ){
			$this->deleteUserTask( $taskId );
			$this->deleteTask( $taskId );
		}
	}

	private function deleteUserSurfaceDone( $surfaceId ){
		$query = 'DELETE FROM user_surface_done WHERE id_surface IN ( ' . implode( ',', $surfaceId ) . ' )';
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        echo "Error while deleting user_surface_done";
                }
	}

        private function deleteUserSurfaceLock( $surfaceId ){
                $query = 'DELETE FROM user_surface_lock WHERE id_surface IN ( ' . implode( ',', $surfaceId ) . ' )';
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        echo "Error while deleting user_surface_lock";
                }
        }

        private function deleteUserSurfaceExpandedLock( $surfaceId ){
                $query = 'DELETE FROM user_surface_expanded_lock WHERE id_surface IN ( ' . implode( ',', $surfaceId ) . ' )';
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        echo "Error while deleting user_surface_expanded_lock";
                }
        }

        private function deleteUserSurfaceFlag( $surfaceId ){
                $query = 'DELETE FROM user_surface_flag WHERE id_surface IN ( ' . implode( ',', $surfaceId ) . ' )';
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        echo "Error while deleting user_surface_flag";
               }
        }

        private function deleteSurface( $surfaceId ){
                $query = 'DELETE FROM surface WHERE id_surface IN ( ' . implode( ',', $surfaceId ) . ' )';
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        echo "Error while deleting surface";
                }
        }

        private function deleteUserTask( $taskId ){
                $query = 'DELETE FROM user_task WHERE id_task = ' . $taskId;
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        echo "Error while deleting user_task";
                }
        }

        private function deleteTask( $taskId ){
                $query = 'DELETE FROM task WHERE id_task = ' . $taskId;
                if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        echo "Error while deleting task";
                }
        }

	public function getSurfaceCount( $taskId ){
		$query = 'SELECT COUNT( DISTINCT surface.id_surface ) AS cs
			FROM surface
			INNER JOIN candidate ON candidate.id_surface_form = surface_form.id_surface_form
			INNER JOIN task_content ON task_content.id_candidate = candidate.id_candidate
			WHERE task_content.id_task = ' . $taskId;
		$res = $this->db->QuerySingleRowArray( $query, MYSQLI_ASSOC );
                if( $res === false ){
                        return -1;
                }
                return $res[ 'cs' ];
	}
	
	public function getValidatedCandidateCount( $taskId ){
		$query = 'SELECT COUNT( DISTINCT user_surface_done.id_candidate ) AS cs
                        FROM user_surface_done';
		$res = $this->db->QuerySingleRowArray( $query, MYSQLI_ASSOC );
		if( $res === false ){
			return -1;
		}
		return $res[ 'cs' ];
	}

	public function getCountValidatedPerLang(){
                $query = 'SELECT COUNT( DISTINCT user_surface_done.id_candidate ) as cc,
                                lang.id_lang, lang.shortname_lang, lang.longname_lang
                        FROM user_surface_done
			INNER JOIN surface ON surface.id_surface = user_surface_done.id_surface
                        INNER JOIN lang ON lang.id_lang = surface.lang_surface
                        GROUP BY lang.id_lang
                        ';
                $res = $this->db->QueryArray( $query, MYSQLI_ASSOC );
                if( $res === false ){
                        return -1;
                }
                return $res;
        }

	public function getCountValidatedPerCategory(){
		$query = 'SELECT COUNT( DISTINCT user_surface_done.id_candidate ) as cc,
				pos.label_pos, pos.short_pos
			FROM user_surface_done
			INNER JOIN pos ON pos.id_pos = user_surface_done.id_pos
			GROUP BY pos.id_pos
			';
		$res = $this->db->QueryArray( $query, MYSQLI_ASSOC );
		if( $res === false ){
                        return -1;
                }
                return $res;
        }

	public function getAllCounts(){
		$toreturn = array( 
			//"Tasks" => $this->getCount( "task" ),
			"Surface forms" => $this->getCount( "surface" ),
			"Annotated surface forms" => $this->getValidatedSurface(),
			"Lemmas" => $this->getCount( "lemma" ),
			"Paradigms" => $this->getCount( "paradigm" ),
			"Candidates" => $this->getCount( "candidate" ),
			"Validated candidates" => $this->getValidatedCandidate(),
		);
		return $toreturn;
	}

	private function getValidatedCandidate(){
		$query = "SELECT COUNT( DISTINCT id_candidate ) as cc
			FROM user_surface_done
			";
		if( !$this->db->Query( $query ) ){
                      $this->db->Kill();
                      return -1;
                }
                $this->db->MoveFirst();
                $row = $this->db->Row();
                return $row->cc;
	}

	private function getValidatedSurface(){
		$query = "SELECT COUNT( DISTINCT id_surface ) AS cs
			FROM user_surface_done
			";
		if( !$this->db->Query( $query ) ){
                      $this->db->Kill();
                      return -1;
                }
                $this->db->MoveFirst();
                $row = $this->db->Row();
                return $row->cs;
	}

	private function getCount( $item ){
		$query = "SELECT COUNT( DISTINCT id_" . $item . " ) AS co FROM " . $item;
		if( !$this->db->Query( $query ) ){
                      $this->db->Kill();
                      return -1;
                }
                $this->db->MoveFirst();
                $row = $this->db->Row();
                return $row->co;
	}		

	public function activateTask( $idTask ){
		$query = "UPDATE task SET task.activate_task = !task.activate_task WHERE task.id_task = " . $idTask;
		if( !$this->db->Query( $query ) ){
                      $this->db->Kill();
                      return 0;
                }
		return 1;
	}

	public function getLanguages(){
		$lang = array();
		$query = "SELECT id_lang, shortname_lang, longname_lang FROM lang ORDER BY shortname_lang ASC";
		$res = $this->db->QueryArray( $query, MYSQLI_ASSOC );
		if( $res === false ){
                        return -1;
                }
                return $res;
	}

	public function updateTaskLang( $idTask, $idLang ){
		$this->db->UpdateRows( 'task', array( 'id_lang' => $idLang ), array( 'id_task' => $idTask ) );
	}

	public function crossValidateTask( $idTask ){
		$res = $this->db->QuerySingleRowArray( 'SELECT task.cross_validate FROM task WHERE task.id_task = ' . $idTask, MYSQLI_NUM );
		if( $res[ 0 ] == 1 or $res[ 0 ] == '1' ){ $cv = 0; }
		else{ $cv = 1; }
		$query = $this->db->UpdateRows( 'task', array( 'cross_validate' => $cv ), array( 'id_task' => $idTask ) );
	}
}


/*if( isset( $_POST ) && isset( $_POST[ "jsonfile" ] )  && !empty( $_POST[ "jsonfile" ] ) && $_POST[ "jsonfile" ] != "" ){
	$json_content = null;
	try{
		 $json_content = json_decode( $_POST[ "jsonfile" ], true );
	}catch( Exception $e ){
		echo "Error while decoding JSON file!";
		exit();
	}
	$taskutils = new TaskUtils();
	try{
                $taskutils->parseData( $json_content );
        }catch( Exception $e ){
                echo "Error while parsing JSON!";
		exit();
        }

	try{
		$taskutils->writeUploadedTask( $json_content );
	}catch( Exception $e ){
		echo "Error writing file on server!";
		exit();
	}
	$task = new Task( $taskutils );
	echo json_encode( array( 'new_surface' => $task->newSurfaceForms, 'duplicate' => $task->duplicateSurfaceForms ), JSON_FORCE_OBJECT );
}*/

//print_r( $_GET );

if( isset( $_GET[ 'delete_full_task' ] ) && !empty( $_GET[ 'delete_full_task' ] ) ){
        $task = new TaskUtils();
        echo json_encode( $task->deleteFullTask( $_GET[ 'delete_full_task' ] ), JSON_FORCE_OBJECT );
}

if( isset( $_GET[ 'associate_user' ] ) && !empty( $_GET[ 'associate_user' ] ) && isset( $_GET[ 'task' ] ) && !empty( $_GET[ 'task' ] ) ){
	$task = new TaskUtils();
        echo json_encode( $task->associateUser( $_GET[ 'task' ], $_GET[ 'associate_user' ] ), JSON_FORCE_OBJECT );
}

if( isset( $_GET[ 'change_language' ] ) && !empty( $_GET[ 'change_language' ] ) && isset( $_GET[ 'task' ] ) && !empty( $_GET[ 'task' ] ) ){
        $task = new TaskUtils();
        echo json_encode( $task->updateTaskLang( $_GET[ 'task' ], $_GET[ 'change_language' ] ), JSON_FORCE_OBJECT );
}

if( isset( $_GET[ 'get_users_task' ] ) && !empty( $_GET[ 'get_users_task' ] ) ){
        $task = new TaskUtils();
        echo json_encode( $task->getAssociatedUsers( $_GET[ 'get_users_task' ] ), JSON_FORCE_OBJECT );
}

if( isset( $_GET[ 'get_users_notask' ] ) && !empty( $_GET[ 'get_users_notask' ] ) ){
        $task = new TaskUtils();
        echo json_encode( $task->getNotAssociatedUsers( $_GET[ 'get_users_notask' ] ), JSON_FORCE_OBJECT );
}

if( isset( $_GET[ 'get_details' ] ) && !empty( $_GET[ 'get_details' ] ) ){
        $task = new TaskUtils();
        echo json_encode( $task->getTaskDetails( $_GET[ 'get_details' ] ), JSON_FORCE_OBJECT );
}

if( isset( $_GET[ "list" ] ) && $_GET[ "list" ] == "all" ){
        $task = new TaskUtils();
        echo json_encode( $task->getTasks(), JSON_FORCE_OBJECT );
}

if( isset( $_POST[ "overview" ] ) && $_POST[ "overview" ] == "all" ){
	$task = new TaskUtils();
	$toreturn = $task->getAllCounts();
	echo json_encode( $toreturn, JSON_FORCE_OBJECT );
}

if( isset( $_POST[ "overview" ] ) && $_POST[ "overview" ] == "validated_counts" ){
        $task = new TaskUtils();
        $toreturn = $task->getCountValidatedPerCategory();
        echo json_encode( $toreturn, JSON_FORCE_OBJECT );
}

if( isset( $_POST[ "overview" ] ) && $_POST[ "overview" ] == "validated_counts_lang" ){
        $task = new TaskUtils();
        $toreturn = $task->getCountValidatedPerLang();
        echo json_encode( $toreturn, JSON_FORCE_OBJECT );
}

if( isset( $_GET[ "activate" ] ) && !empty( $_GET[ "activate" ] ) ){
	$task = new TaskUtils();
	echo $task->activateTask( $_GET[ "activate" ] );
}

if( isset( $_GET[ "xval" ] ) && !empty( $_GET[ "xval" ] ) ){
        $task = new TaskUtils();
        echo $task->crossValidateTask( $_GET[ "xval" ] );
}

if( isset( $_GET[ "getlang" ] ) && $_GET[ "getlang" ] == "all" ){
	$task = new TaskUtils();
	echo json_encode( $task->getLanguages(), JSON_FORCE_OBJECT );
}

if( isset( $_GET[ "setlang" ] ) && isset( $_GET[ "value" ] ) ){
	$task = new TaskUtils();
	$task->updateTaskLang( $_GET[ "setlang" ], $_GET[ "value" ] );
}

?>
