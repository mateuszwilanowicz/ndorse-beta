<?php
    class Ndoorse_Serviceprovider extends Model {
        
        protected $serviceproviderID;
        protected $name;
        protected $description;
        protected $locationID;
        protected $location;
        protected $status;
        protected $logoID;
        protected $logoURL;
        protected $industry;
        protected $attributes;
        protected $members;
        protected $documentID; 
        protected $content;
        protected $documentURL;
        protected $url;
        protected $contactUsEmail;
        protected $userID;
        protected $twitterFeed;
        protected $twitterFeedUser;
                
        const STATUS_INACTIVE = 0;
        const STATUS_PENDING = 1;       // service provider profile awaiting approval
        const STATUS_ACTIVE = 2;

        const MEMBER_STATUS_INVITED = 0;
        const MEMBER_STATUS_DECLINED = 1;
        const MEMBER_STATUS_ACCEPTED = 2;
        const MEMBER_STATUS_OWNER = 3;

        public function __construct($params = null) {
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
            $this->attributes = new Map();
        }
        
        public function loadByID($id) {
            $dbConn = DatabaseConnection::getConnection();
            $sql = 'SELECT s.*, l.location, d.filePath as logoURL, o.filePath as documentURL, GROUP_CONCAT(sa.value SEPARATOR ",") as value
                    FROM ndoorse_serviceprovider s
                    LEFT OUTER JOIN ndoorse_location l
                        USING(locationID)
                    LEFT OUTER JOIN ndoorse_document d
                        ON(s.logoID = d.documentID)
                    LEFT OUTER JOIN ndoorse_document o
                        ON(s.documentID = o.documentID)
                    LEFT OUTER JOIN ndoorse_serviceprovider_attribute sa
                        ON(sa.serviceproviderID = s.serviceproviderID)
                    WHERE s.status = 2
                    AND s.serviceproviderID = :serviceproviderID
                    ';


            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('serviceproviderID', $id);
            $result = $stmt->execute();
    
            if($result instanceof Resultset && $result->hasRows()) {
                $row = $result->nextRow();
                $i = $this->loadFromArray($row);
                return $i;
            }
            return false;
        }
        
        public static function getIndustries() {
            $dbConn = DatabaseConnection::getConnection();
            $sql = 'SELECT DISTINCT (value) FROM ndoorse_serviceprovider_attribute';
            $stmt = $dbConn->prepareStatement($sql);
            $result = $stmt->execute();
            $return = array();
            $return[] = array('value'=>'', 'label'=>'(select)');
            if($result instanceof Resultset && $result->hasRows()) {
                while($row = $result->nextRow()) {
                    $instance = new Ndoorse_Member($row);
                    $return[] = array('value'=>$row['value'], 'label'=>$row['value']);
                }
            }
            return $return;    
        }
        
        public static function getAllProviders($args) {
            $return = array();
            $params = array();
            $dbConn = DatabaseConnection::getConnection();
            $sql = 'SELECT s.*, l.location, d.filePath as logoURL, GROUP_CONCAT(sa.value SEPARATOR ",") as value
                    FROM ndoorse_serviceprovider s
                    LEFT OUTER JOIN ndoorse_location l
                        USING(locationID)
                    LEFT OUTER JOIN ndoorse_document d
                        ON(s.logoID = d.documentID)
                    LEFT OUTER JOIN ndoorse_serviceprovider_attribute sa
                        ON(sa.serviceproviderID = s.serviceproviderID)
                    WHERE s.status = 2
                    ';
            if(isset($args['keywords']) && !empty($args['keywords'])) {
                $join = isset($args['keywordoptions']) && $args['keywordoptions'] == 'all' ? ' AND ' : ' OR ';

                $keywords = explode(' ', $args['keywords']);
                $sql .= 'AND (';
                $tmp = array();
                for($i = 0; $i < count($keywords); ++$i) {
                    $tmp[] = '(description LIKE :keyw' . $i . ')';
                    $params['keyw' . $i] = '%' . $keywords[$i] . '%';
                }
                $sql .= implode($join, $tmp);
                $sql .= ') ';
            }
            if(isset($args['location']) && !empty($args['location'])) {
                $sql .= ' AND locationID = :location';
                $params['location'] = $args['location'];
            }
            if(isset($args['industry']) && !empty($args['industry'])) {
                $sql .= ' AND sa.value = :industry';
                $params['industry'] = $args['industry'];
            }
            $sql .= ' GROUP BY(s.serviceproviderID)';
            $stmt = $dbConn->prepareStatement($sql);
            foreach($params as $key=>$val) {
                $stmt->bindParameter($key, $val);
            }
            
            
            //$stmt->bindParameter('serviceproviderID', $id);
            $result = $stmt->execute();
    
            if($result instanceof Resultset && $result->hasRows()) {
                while($row = $result->nextRow()) {
                    $instance = new Ndoorse_Serviceprovider($row);
                    //$instance->loadAttributes();
                    //$instance->loadMembers();
                    $return[] = $instance;
                }
                return $return;
            }
            return array();
        }
        
        public function save() {
            return $this->saveModel(array('location','attributes','industry','logoURL','members','documentURL'));
        }
        
        public function loadMembers() {
            $dbConn = DatabaseConnection::getConnection();
            $sql = 'SELECT sm.serviceproviderID, sm.memberID, sm.status as memberStatus, sm.position, m.*
                    FROM ndoorse_serviceprovider_member sm
                    LEFT OUTER JOIN ndoorse_member m
                        ON (sm.memberID = m.userID)
                    WHERE sm.serviceproviderID = :serviceproviderID
                    ';
                    
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('serviceproviderID', $this->serviceproviderID);
            
            $result = $stmt->execute();
    
            if($result instanceof Resultset && $result->hasRows()) {
                $this->members = array();
                while($row = $result->nextRow()) {
                    
                    $instance = new Ndoorse_Member($row);
                    $this->members[] = array($instance, $row['memberStatus'], $row['position']);
                    //$this->members[] = $instance;
                }
                //pr($this->members);
                return true;
            }
            $this->members = array();
            return false;
            
        }
        public static function getUserStatus($s) {
            switch ($s) {
                case 0:
                    return 'Invited';
                    break;
                case 1:
                    return 'Declined';
                    break;
                case 2:
                    return 'Accepted';
                    break;
                case 3:
                    return 'Owner';
                    break;
                default:
                    return 'No status!';
                    break;
            }
        }
        
        public static function addIndustryAttribute($serviceProviderID,$value) {
            $dbConn = DatabaseConnection::getConnection();
            //TO MAKE SURE ONLY ONE INDUSTRY PER SERVICE PROVIDER
            $sql = 'DELETE FROM ndoorse_serviceprovider_attribute WHERE serviceProviderID = :serviceProviderID AND attributeKey = "INDUSTRY"';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('serviceProviderID', $serviceProviderID);
            $result = $stmt->execute();
           
            $sql = 'REPLACE INTO ndoorse_serviceprovider_attribute VALUES (:serviceProviderID,"INDUSTRY", :value)';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('value', $value);
            $stmt->bindParameter('serviceProviderID', $serviceProviderID);
            $result = $stmt->execute();

            return $result;  
            
        }
        
        public function loadAttributes($includeEmpties = false) {
            $dbConn = DatabaseConnection::getConnection();

            $sql = <<<SQL
            SELECT * FROM core_attribute a
                LEFT JOIN ndoorse_serviceprovider_attribute sa
                ON sa.attributeKey = a.`key`
                WHERE a.`type` = 'SERVICEPROVIDER'
                AND
SQL;
               
            $sql .= ' sa.serviceproviderID = :serviceproviderID';

            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('serviceproviderID', $this->serviceproviderID);
            
            $attributedata = $stmt->execute();
            $this->attributes = new Map();
            for ($i = 0; $i < $attributedata->getRowCount(); $i++) {
                $row = $attributedata->nextRow();
                $tmpAttribute = Attribute::loadAttribute($row);
                $this->attributes->add($row['key'], $tmpAttribute);
            }
            //pr($this->attributes);
        }
        
        public function inviteMember($memberID,$position = '') {
            $dbConn = DatabaseConnection::getConnection();
            
            $sql = 'REPLACE INTO ndoorse_serviceprovider_member VALUES (:serviceProviderID,:userID,0,:position)';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('userID', $memberID);
            $stmt->bindParameter('serviceProviderID', $this->serviceproviderID);
            $stmt->bindParameter('position', $position);
            $result = $stmt->execute();
            if($result) {
                $name = $_SESSION['user']->firstname . ' ' .$_SESSION['user']->lastname;
                $content = array('name'=>$name, 'serviceprovidername'=>$this->name, 'description'=>$this->description, 'url'=>$this->url);
                
                $message = new Ndoorse_Message();
                $message->loadTemplate('serviceprovider_invite', $content);
    
                $message->senderID = $_SESSION['user']->getID();
                $message->subject = 'Serviceprovider Invitation';
                $message->type = Ndoorse_Message::TYPE_SERVICEPROVIDER_INVITE;
                $message->data = $this->serviceproviderID;
    
                try {
                    $message->send(array($memberID));
                    $_SESSION['page_messages'][] = 'Your serviceprovider invitation has been sent';
                    redirect(BASE_URL . 'serviceproviders/post/' . $this->serviceproviderID . '/');
                } catch(Exception $e) {
                    $_SESSION['page_errors'][] = 'Your serviceprovider invitation could not be sent';
                }
            }
            
            return $result;
        }


        
        public function acceptInvite($memberID) {
            $dbConn = DatabaseConnection::getConnection();
            $sql = 'UPDATE ndoorse_serviceprovider_member SET status = :status WHERE serviceproviderID = :serviceproviderID AND memberID = :memberID';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('memberID', $memberID);
            $stmt->bindParameter('serviceproviderID', $this->serviceproviderID);
            $stmt->bindParameter('status', Ndoorse_Serviceprovider::MEMBER_STATUS_ACCEPTED);
            $result = $stmt->execute();
            return $result;
        }

        public function ignoreInvite($memberID) {
            $dbConn = DatabaseConnection::getConnection();
            $sql = 'DELETE FROM ndoorse_serviceprovider_member WHERE serviceproviderID = :serviceproviderID AND memberID = :memberID';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('memberID', $memberID);
            $stmt->bindParameter('serviceproviderID', $this->serviceproviderID);
            $result = $stmt->execute();
            return $result;
        }
        
        public static function autocomplete($text) {

            if(empty($text)) {
                return array();
            }

            $dbConn = DatabaseConnection::getConnection();
            $stmt = $dbConn->prepareStatement('SELECT * FROM ndoorse_serviceprovider WHERE name LIKE :nam');
            $stmt->bindParameter('nam', $text . '%');

            $result = $stmt->execute();
            if($result instanceof Resultset && $result->hasRows()) {
                return $result->getResultsetAsArray();
            }
            return array();

        }
        

    }
?>