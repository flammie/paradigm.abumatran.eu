<?php
require_once( "mysql.class.php" );

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
                $query = "INSERT INTO task ( date_add_task, activate_task ) VALUES ( '" . date( "YmdHis" ) . "', 0 )";
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
		echo "oui";
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
		$tasks = array();
		$query = "SELECT task.id_task, task.date_add_task, task.activate_task, task.cross_validate, lang.shortname_lang
			FROM task, lang
			WHERE task.lang_task = lang.id_lang
			OR task.lang_task IS NULL
			GROUP BY task.id_task
			ORDER BY task.date_add_task DESC";
		if( !$this->db->Query( $query ) ){
                        $this->db->Kill();
                        echo "Database error";
                        exit();
                }
               	$this->db->MoveFirst();
		while( !$this->db->EndOfSeek() ){
			$row = $this->db->Row();
			$tasks[] = array( "id_task" => $row->id_task, "lang_task" => $row->shortname_lang, "date_add_task" => $row->date_add_task, "activate_task" => $row->activate_task, "surface_count" => 0, "validated_candidates" => 0 );
		} 
		foreach( $tasks as $key => $val ){
			$tasks[ $key ][ "surface_count" ] = $this->getSurfaceCount( $val[ "id_task" ] );
			$validatedCandidateCount = $this->getValidatedCandidateCount( $val[ "id_task" ] );
			if( $validatedCandidateCount == null ){ $validatedCandidateCount = 0; }
			$tasks[ $key ][ "validated_candidates" ] = $validatedCandidateCount;
		}
		return json_encode( $tasks, JSON_FORCE_OBJECT );
	}
	
	public function getSurfaceCount( $taskId ){
		$query = "SELECT COUNT( DISTINCT surface_form.id_surface_form ) AS cs
			FROM surface_form, candidate, task_content 
			WHERE task_content.id_task = " . $taskId . "
			AND task_content.id_candidate = candidate.id_candidate
			AND candidate.id_surface_form = surface_form.id_surface_form
			";
		if( !$this->db->Query( $query ) ){
                      $this->db->Kill();
                      return -1;
                }
                $this->db->MoveFirst();
                $row = $this->db->Row();
                return $row->cs;
	}

	public function getValidatedCandidateCount( $taskId ){
		$query = "SELECT COUNT( DISTINCT surface_form.id_surface_form ) AS cs
                        FROM surface_form, candidate, task_content, user_validate
                        WHERE task_content.id_task = " . $taskId . "
                        AND task_content.id_candidate = candidate.id_candidate
                        AND candidate.id_surface_form = surface_form.id_surface_form
			AND user_validate.id_candidate = candidate.id_candidate
			GROUP BY candidate.id_surface_form
                        ";
		if( !$this->db->Query( $query ) ){
                      $this->db->Kill();
                }elseif( $this->db->RowCount() > 0 ){
	                $this->db->MoveFirst();
        	        $row = $this->db->Row();
                	return $row->cs;
		}
		return -1;
	}

	public function getCountValidatedPerCategory(){
                $count = array();
                $categories = array( "n", "v", "a" );
                foreach( $categories as $cat ){
                        $query = "      SELECT COUNT( subcand.id_surface_form ) AS countSurface
                                FROM (
                                        SELECT candidate.id_candidate, candidate.id_paradigm, candidate.id_surface_form
                                        FROM candidate
					INNER JOIN user_validate ON user_validate.id_candidate = candidate.id_candidate
                                        INNER JOIN paradigm ON paradigm.id_paradigm = candidate.id_paradigm
                                        INNER JOIN surface_form ON surface_form.id_surface_form = candidate.id_surface_form
                                        INNER JOIN lemma ON lemma.id_lemma = candidate.id_lemma
                                        WHERE surface_form.id_surface_form NOT IN ( SELECT surface_form_flag.id_surface_form FROM surface_form_flag )
                                        GROUP BY candidate.id_surface_form
                                        ORDER BY candidate.probability DESC
                                ) AS subcand
                                INNER JOIN paradigm ON paradigm.id_paradigm = subcand.id_paradigm
                                WHERE paradigm.value_paradigm LIKE '%\_\_" . $cat . "%'
                                ";
			if( !$this->db->Query( $query ) ){
	 	        	$this->db->Kill();
                  		return -1;
			}
	                $this->db->MoveFirst();
        	        $row = $this->db->Row();
                        $count[ $cat ] = $row->countSurface;
                }
                return $count;
        }

	public function getAllCounts(){
		$toreturn = array( 
			"Tasks" => $this->getCount( "task" ),
			"Surface forms" => $this->getCount( "surface_form" ),
			"Annotated surface forms" => $this->getValidatedSurface(),
			"Lemma" => $this->getCount( "lemma" ),
			"Paradigm" => $this->getCount( "paradigm" ),
			"Candidates" => $this->getCount( "candidate" ),
			"Validated candidates" => $this->getValidatedCandidate(),
		);
		return $toreturn;
	}

	private function getValidatedCandidate(){
		$query = "SELECT COUNT( DISTINCT candidate.id_candidate ) as cc
			FROM candidate
			INNER JOIN user_validate ON user_validate.id_candidate = candidate.id_candidate";
		if( !$this->db->Query( $query ) ){
                      $this->db->Kill();
                      return -1;
                }
                $this->db->MoveFirst();
                $row = $this->db->Row();
                return $row->cc;
	}

	private function getValidatedSurface(){
		$query = "SELECT COUNT( DISTINCT surface_form.id_surface_form ) AS cs, candidate.id_candidate
			FROM surface_form, candidate
			INNER JOIN user_validate ON user_validate.id_candidate = candidate.id_candidate
			WHERE candidate.id_surface_form = surface_form.id_surface_form";
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
		$query = "SELECT id_lang, shortname_lang FROM lang ORDER BY shortname_lang ASC";
		if( !$this->db->Query( $query ) ){
                      $this->db->Kill();
                      return 0;
                }
		$this->db->MoveFirst();
                while( !$this->db->EndOfSeek() ){
                        $row = $this->db->Row();
                        $lang[] = array( "id_lang" => $row->id_lang, "shortname_lang" => $row->shortname_lang );
                }
		return $lang;
	}

	public function updateTaskLang( $idTask, $idLang ){
		$query = "UPDATE task SET lang_task = " . $idLang . " WHERE id_task = " . $idTask;
		if( !$this->db->Query( $query ) ){
                      $this->db->Kill();
                      return 0;
                }
		return 1;
	}
}


if( isset( $_POST ) && isset( $_POST[ "jsonfile" ] )  && !empty( $_POST[ "jsonfile" ] ) && $_POST[ "jsonfile" ] != "" ){
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
}

if( isset( $_GET[ "list" ] ) && $_GET[ "list" ] == "all" ){
        $task = new TaskUtils();
        echo $task->getTasks();
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

if( isset( $_GET[ "activate" ] ) && !empty( $_GET[ "activate" ] ) ){
	$task = new TaskUtils();
	echo $task->activateTask( $_GET[ "activate" ] );
}

if( isset( $_POST[ "getlang" ] ) && $_POST[ "getlang" ] == "all" ){
	$task = new TaskUtils();
	echo json_encode( $task->getLanguages(), JSON_FORCE_OBJECT );
}

if( isset( $_GET[ "setlang" ] ) && isset( $_GET[ "value" ] ) ){
	$task = new TaskUtils();
	$task->updateTaskLang( $_GET[ "setlang" ], $_GET[ "value" ] );
}
?>
