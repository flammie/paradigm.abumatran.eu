<?php

require_once( "mysql.class.php" );

class Export{

	private $db;
        private $format;
	private $idLangs;
	private $exportData;
	private $fileName;

	public function Export( $format, $idTask ){
		$this->db = new MySQL();
                $this->setDBEncoding( $this->db );
		$this->format = $format;
		$this->idTask = $idTask;
		$this->exportData = array();
	}

        private function setDBEncoding( &$db ){
                $query = "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'";
                if( !$db->Query( $query ) ){
                        $db->Kill();
                        echo "Error with DB encoding configuration query!";
                }
        }

	public function retrieveTasks(){
		$validatedCandidates = $this->getValidatedCandidates( $this->idTask );
		$this->exportData = array_merge( $this->exportData, $validatedCandidates );
		$this->createFile();
		$this->createLink();
	}

	private function getValidatedCandidates( $idTask ){
		$query = 'SELECT user_surface_done.id_candidate, lemma.value_lemma, paradigm.value_paradigm,
				specific_type.value_specific_type, user.name_user
			FROM user_surface_done
			INNER JOIN surface ON surface.id_surface = user_surface_done.id_surface
			INNER JOIN candidate ON candidate.id_candidate = user_surface_done.id_candidate
			INNER JOIN lemma ON lemma.id_lemma = candidate.id_lemma
			INNER JOIN paradigm ON paradigm.id_paradigm = candidate.id_paradigm
			INNER JOIN user ON user.id_user = user_surface_done.id_user
			LEFT JOIN user_surface_specific ON user_surface_specific.id_surface = user_surface_done.id_surface
			LEFT JOIN specific_type ON specific_type.id_specific_type = user_surface_specific.id_specific_type
			WHERE surface.id_task = ' . $idTask;
                return $this->db->QueryArray( $query, MYSQLI_ASSOC );
	}

	private function createFile(){
		$towrite = '';
		$ext = '';
		if( $this->format == 'json' ){ 
			$towrite = $this->makeJson(); 
			$ext = $this->format;
		}
		elseif( $this->format == 'apertiumdix' ){ 
			$towrite = $this->makeApertiumDix(); 
			$ext = 'dix';
		}
		$this->fileName = 'export/' . date( "YmdHis" ) . '.' . $ext . '.gz';
		$handler = gzopen( '/var/www/paradigm.abumatran.eu/admin/' . $this->fileName, "w" );
		gzwrite( $handler, $towrite ); 
		gzclose( $handler );
	}

	private function makeJson(){
		return json_encode( $this->exportData );
	}

	private function makeApertiumDix(){
		$written = array();
		$towrite = array();
		$towriteSpecific = array();
		foreach( $this->exportData as $tuple ){
			$array_key = array_key_exists( "'" . $tuple[ 'id_candidate' ] . "'", $towrite );
			if( $array_key === false ){
				$lemma = $tuple[ 'value_lemma' ];
				$paradigm = $tuple[ 'value_paradigm' ];
				$specific = $tuple[ 'value_specific_type' ];
				$user = $tuple[ 'name_user' ];
				$pos = strpos( $paradigm, '/' );
				if( $pos === false ){
					$processedLemma = $lemma;
				}else{
					$underpos = strpos( $paradigm, '__', $pos );
					$processedLemma = substr( $lemma, 0, count( $lemma ) - ( $underpos - $pos ) );
				}
				$towrite[ "'" . $tuple[ 'id_candidate' ] . "'" ] = '<e lm="' . $lemma . '" a="' . $user . '"><i>' . $processedLemma . '</i><par n="' . $paradigm . '"/></e>';
				if( $tuple[ 'value_specific_type' ] ){
					$towrite[ "'" . $tuple[ 'id_candidate' ] . "'" ] .= '<!-- ' . $tuple[ 'value_specific_type' ] . ' -->';
				}
			}else{
				$towrite[ "'" . $tuple[ 'id_candidate' ] . "'" ] .= '<!-- ' . $tuple[ 'value_specific_type' ] . ' -->';
			}
		}
		sort( $towrite, SORT_STRING );
		return implode( "\n", $towrite );
	}

	private function createLink(){
		echo $this->fileName;
	}
}

if( isset( $_POST[ 'task' ] ) && !empty( $_POST[ 'task' ] ) && 
	isset( $_POST[ 'format' ] ) && !empty( $_POST[ 'format' ] ) ){

	$export = new Export( $_POST[ 'format' ], $_POST[ 'task' ] );
	$export->retrieveTasks();
}
?>
