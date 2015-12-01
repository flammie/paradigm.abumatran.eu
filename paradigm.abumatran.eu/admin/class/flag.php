<?php
require_once( "mysql.class.php" );

class Flag{

	private $db = null;

        public function Flag(){
                $this->db = new MySQL();
                $this->setDBEncoding( $this->db );
        }

        private function setDBEncoding( &$db ){
                $query = "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'";
                if( !$db->Query( $query ) ){
                        $db->Kill();
                        echo "Error with DB encoding configuration query!";
                }
        }

	public function getCountFlaggedByTask( $taskId ){
		$query = 'SELECT count( user_surface_flag.id_surface )
                        FROM user_surface_flag
                        INNER JOIN surface ON surface.id_surface = user_surface_flag.id_surface
			INNER JOIN task ON task.id_task = surface.id_task
			WHERE task.id_task = ' . $taskId;

	}

	public function getFlagged( $taskId ){
		$query = 'SELECT user.name_user, surface.value_surface, user_surface_flag.id_surface, 
				flag.value_flag, flag.id_flag
			FROM user_surface_flag
			INNER JOIN surface ON surface.id_surface = user_surface_flag.id_surface
			INNER JOIN task ON task.id_task = surface.id_task
			INNER JOIN user ON user.id_user = user_surface_flag.id_user
                        INNER JOIN flag ON flag.id_flag = user_surface_flag.id_flag
			WHERE task.id_task = ' . $taskId . '
                        ORDER BY surface.value_surface';
		$res = $this->db->QueryArray( $query, MYSQLI_ASSOC );
		return $res;
	}

	public function getFilters( $langId = -1 ){
		$query = 'SELECT flag.id_flag, flag.value_flag, count( user_surface_flag.id_surface ) AS count_flagged
			FROM flag 
			INNER JOIN user_surface_flag ON user_surface_flag.id_flag = flag.id_flag ';
		if( $langId != -1 ){
			$query .= ' WHERE flag.id_lang = ' . $langId;
		}
		$query .= ' GROUP BY flag.value_flag';
		$res = $this->db->QueryArray( $query, MYSQLI_ASSOC );
                return $res;
	}

	public function getExportFile( $tabFlagLabels, $taskId ){
		if( count( $tabFlagLabels ) <= 0 ){
			return -1;
		}
		$tabFlagLabels = array_values( $tabFlagLabels );
		$tabFlagLabels = implode( '","', $tabFlagLabels );
		$tabFlagLabels .= '"';
		$tabFlagLabels = '"' . $tabFlagLabels;
		$query = '
			SELECT user.name_user, surface.value_surface, user_surface_flag.id_surface, flag.value_flag
                        FROM user_surface_flag
			INNER JOIN surface ON surface.id_surface = user_surface_flag.id_surface
			INNER JOIN task ON task.id_task = surface.id_task
                        INNER JOIN user ON user.id_user = user_surface_flag.id_user
                        INNER JOIN flag ON flag.id_flag = user_surface_flag.id_flag
			WHERE task.id_task = ' . $taskId . '
			AND flag.value_flag IN ( ' . $tabFlagLabels . ' )
			GROUP BY surface.id_surface
                        ORDER BY surface.value_surface
			';
		$res = $this->db->QueryArray( $query, MYSQLI_ASSOC );
		$res = $this->formatContent( $res );
		$file = $this->createFile( $res );
		return $file;
	}

	private function formatContent( $content ){
		$toreturn = "";
		foreach( $content as $item ){
			$toreturn .= $item[ 'value_surface' ] . "\t" . $item[ 'value_flag' ] . "\t" . $item[ 'name_user' ] . "\n";
		}
		return $toreturn;
	}

        private function createFile( $towrite ){
                $ext = 'csv';
                $fileName = 'flag/' . date( "YmdHis" ) . '.' . $ext . '.gz';
                $handler = gzopen( '/var/www/paradigm.abumatran.eu/admin/' . $fileName, "w" );
                gzwrite( $handler, $towrite );
		gzclose( $handler );
		return $fileName;
        }

}

if( isset( $_GET[ 'list' ] ) && $_GET[ 'list' ] == 'flags' ){
	$flag = new Flag();
	if( isset( $_GET[ 'task' ] ) && $_GET[ 'task' ] != '-1' ){
		echo json_encode( $flag->getFlagged( $_GET[ 'task' ] ), TRUE );
	}else{
		echo json_encode( $flag->getFlagged(), TRUE );
	}
}

if( isset( $_GET[ 'filter' ] ) && $_GET[ 'filter' ] == 'flags' ){
        $flag = new Flag();
	if( isset( $_GET[ 'lang' ] ) && $_GET[ 'lang' ] != '-1' ){
	        echo json_encode( $flag->getFilters( $_GET[ 'lang' ] ), TRUE );
	}else{
		echo json_encode( $flag->getFilters(), TRUE );
	}
}

if( isset( $_GET[ 'export' ] ) && isset( $_GET[ 'task' ] ) && !empty( $_GET[ 'task' ] ) ){
        $flag = new Flag();
	echo json_encode( $flag->getExportFile( $_GET[ 'export' ], $_GET[ 'task' ] ), TRUE );
}

?>
