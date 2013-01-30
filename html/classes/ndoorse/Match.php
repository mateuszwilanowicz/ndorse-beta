<?php
    class Ndoorse_Match extends Model {

        protected $userID;
        protected $entityID;
        protected $entity;
        protected $applicationID;
        protected $date;
        protected $type;
        protected $status;
        protected $recommendeeID;
        protected $dateModified;
        protected $modifiedBy;
        protected $notes;
        //to be excluded
        protected $firstname;
        protected $lastname;
        protected $email;
        protected $location;

        const STATUS_NOTCONTACED = 0;
        const STATUS_CONTACTED = 1;
        const STATUS_ACCEPTED = 2;
        const STATUS_DECLINED = 3;
        const STATUS_REMOVED = 4;

        const TYPE_MATCHED = 0;
        const TYPE_RECOMMENDED = 1;
        const TYPE_MANUAL = 2;
        const TYPE_APLIED = 3;

        function __construct() {
            $a = func_get_args();
            $i = func_num_args();
            if (method_exists($this,$f='__construct'.$i)) {
                call_user_func_array(array($this,$f),$a);
            }
        }

        public function __construct1($params = null) {

            if(!is_null($params)) {
                if(is_array($params)) {
                    $this->loadFromArray($params);
                } else {
                    if(method_exists($this, 'loadByID')) {
                        $this->loadByID($params);
                    }
                    $this->loadModelByID($params);
                }
            }

        }

        public function __construct3($p1 = null,$p2 = null,$p3 = null) {
            if(!is_null($p1) && !is_null($p2) && !is_null($p3)) {
                $this->loadModelByParams($p1,$p2,$p3);
            }
        }

        public function loadModelByParams($p1,$p2,$p3) {
            $dbConn = DatabaseConnection::getConnection();
            $sql = 'SELECT * FROM ndoorse_match m
                    WHERE entityID = :entityID
                    AND entity = :entity
                    AND userID = :userID';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('entityID', $p1);
            $stmt->bindParameter('entity', $p2);
            $stmt->bindParameter('userID', $p3);
            $result = $stmt->execute();
            $output = array();
            if($result instanceof Resultset && $result->hasRows()) {
                $row = $result->nextRow();
                return $this->loadFromArray($row);
            } else {
                $row = array($p1,$p2,$p3);
                return $this->loadFromArray($row);
            }
        }

        public function save($excludes = array(), $idfield = null) {
            //pr($this);
            $excludes = array_merge($excludes,array('firstname','lastname','email', 'location'));
            $dbConn = DatabaseConnection::getConnection();
            $class = get_class($this);
            $sql = 'REPLACE ';
            $sql .=  strtolower($class) . ' SET ';
            $vals = array();
            $sets = array();
            foreach($this as $key=>$val) {
                if(!in_array($key, $excludes) && $key != 'attributes') {
                    $sets[$key] = '`' . $key . '` = :' . $key;
                }
            }
            $sql .= implode(', ', $sets);
            if(!is_null($idfield)) {
                $suffix = $idfield;
            } else {
                $tmp = explode('_', $class);
                $suffix = count($tmp) > 1 ? $tmp[1] : $class;
                $idfield = strtolower($suffix) . 'ID';
            }
            $stmt = $dbConn->prepareStatement($sql);
            foreach($this as $key=>$val) {
                if(!in_array($key, $excludes) && $key != 'attributes')
                    $stmt->bindParameter($key, $this->$key);
            }
            $result = $stmt->execute();

            return $result;
        }

        static function exists($userID, $entityID, $entity) {
            $dbConn = DatabaseConnection::getConnection();
            $sql = 'SELECT * FROM ndoorse_match m
                    WHERE entityID = :entityID
                    AND entity = :entity
                    AND userID = :userID';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('entityID', $entityID);
            $stmt->bindParameter('entity', $entity);
            $stmt->bindParameter('userID', $userID);
            $result = $stmt->execute();
            $output = array();
            if($result instanceof Resultset && $result->hasRows()) {
               return true;
            }
            return false;
        }

        static function getbyids($userID, $entityID, $entity) {
            $dbConn = DatabaseConnection::getConnection();
            $sql = 'SELECT * FROM ndoorse_match m
                    WHERE entityID = :entityID
                    AND entity = :entity
                    AND userID = :userID';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('entityID', $entityID);
            $stmt->bindParameter('entity', $entity);
            $stmt->bindParameter('userID', $userID);
            $result = $stmt->execute();
            $output = array();
            if($result instanceof Resultset && $result->hasRows()) {
                $row = $result->nextRow();
                return new Ndoorse_Match($row);
            }
            return false;
        }

        static function getMatchesFor($entityID,$entity,$orderby = 'lastname', $dir = 'asc') {
            if(empty($entityID) || empty($entity)) {
                throw new Exception('Ndoorse/Match/getMatchesFor: No ID');
            }
            switch($orderby) {
                case 'name':
                    $orderby = 'lastname';
                    break;
                case 'type':
                    $orderby = 'type';
                    break;
                case 'status':
                    $orderby = 'm.status';
                    break;
                case 'application':
                    $orderby = 'applicationID';
                    break;
                case 'recommendee':
                    $orderby = 'recommendeeID';
                    break;
                case 'date':
                    $orderby = 'date';
                    break;
                default:
                    $orderby = 'lastname';
                    break;
            }
            $dir = $dir == 'asc' ? 'asc' : 'desc';
            $dbConn = DatabaseConnection::getConnection();
            $sql = 'SELECT m.*,u.firstname, u.lastname, l.location
                    FROM ndoorse_match m
                    LEFT JOIN ndoorse_member u ON u.userID = m.userID
                    LEFT JOIN ndoorse_location l on u.locationID = l.locationID
                    WHERE entityID = :entityID
                    AND entity = :entity';
            $sql .= ' ORDER BY ' . $orderby . ' ' . $dir;
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('entityID', $entityID);
            $stmt->bindParameter('entity', $entity);
            $result = $stmt->execute();
            $output = array();
            if($result instanceof Resultset && $result->hasRows()) {
                while($row = $result->nextRow())
                    $output[] = new Ndoorse_Match($row);
            }
            return $output;
        }

        static function getNumberOfMatches($entity) {
            if(empty($entity)) {
                throw new Exception('Ndoorse/Match/getMatchesFor: No Entity');
            }
            $entityClass = get_class($entity);

            $dbConn = DatabaseConnection::getConnection();
            $sql = 'SELECT COUNT(*) as num
                    FROM ndoorse_match m
                    WHERE entityID = :entityID
                    AND entity = "job"';

            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('entityID', $entity->getID());
            $result = $stmt->execute();
            if($result instanceof Resultset && $result->hasRows()) {
                $row = $result->nextRow();
                return $row['num'];
            }
            return 0;
        }

        static function generateMatchesFor($entity) {
            if(empty($entity)) {
                throw new Exception('Ndoorse/Match/renewMatches: No Entity');
            }
            $dbConn = DatabaseConnection::getConnection();
            $sql = 'SELECT *, GROUP_CONCAT(s.name SEPARATOR ",") AS skills
                      FROM ndoorse_member m
                      LEFT JOIN ndoorse_education e on e.userID = m.userID
                      LEFT JOIN ndoorse_experience x on x.userID = m.userID
                      LEFT JOIN ndoorse_memberskill ms ON ( e.educationID = ms.entityID AND ms.entity = "education" ) OR ( x.experienceID = ms.entityID AND ms.entity = "experience" )
                      LEFT JOIN ndoorse_skill s ON s.skillID = ms.skillID
                      WHERE
                        MATCH(s.name) AGAINST ( :skills )
                      OR
                        MATCH(e.courseName, e.description) AGAINST ( :skills )
                      OR
                        MATCH(e.courseName, e.description) AGAINST ( :title )
                      OR
                        MATCH(x.jobTitle, x.description) AGAINST ( :skills )
                      OR
                        MATCH(x.jobTitle, x.description) AGAINST ( :title )
                      GROUP BY m.userID
                    ';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('skills', $entity->skills);
            $stmt->bindParameter('title', $entity->title);
            //pr($stmt);
            $result = $stmt->execute();
            $output = array();
            if($result instanceof Resultset && $result->hasRows()) {
                while($row = $result->nextRow()) {
                    $exits = Ndoorse_Match::exists($row['userID'], $entity->jobID, 'job');
                    if($exits) {
                        $userID = $row['userID'];
                        $match = new Ndoorse_Match();
                        $match->status = 0;
                        $match->entity = 'job';
                        $match->userID = $userID;
                        $match->entityID = $entity->jobID;
                        $match->modifiedBy = $_SESSION['user']->userID;
                        $match->type = Ndoorse_Match::TYPE_MATCHED;
                    } else {
                        $match = Ndoorse_Match::getbyids($row['userID'], $entity->jobID, 'job');
                    }
                    $output[] = $match;
                }
            }
            return $output;
        }

        public function delete() {
            if(!empty($this->userID)&&!empty($this->entityID)&&!empty($this->entity)) {
                $dbConn = DatabaseConnection::getConnection();
                $sql = 'DELETE FROM ndoorse_match m
                        WHERE entityID = :entityID
                        AND entity = :entity
                        AND userID = :userID';
                $stmt = $dbConn->prepareStatement($sql);
                $stmt->bindParameter('entityID', $this->entityID);
                $stmt->bindParameter('entity', $this->entity);
                $stmt->bindParameter('userID', $this->userID);
                $result = $stmt->execute();

                if($result instanceof Resultset && $result->hasRows()) {
                    $row = $result->nextRow();
                    return true;
                }
                return false;
            } else {
                return false;
            }

        }

    }
?>