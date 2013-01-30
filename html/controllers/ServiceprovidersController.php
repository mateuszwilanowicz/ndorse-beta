<?php
    class ServiceprovidersController extends Controller {

        public function __construct() {
            parent::__construct(SITE_NAME . ' Serviceproviders', defined('SITE_TEMPLATE') ? SITE_TEMPLATE : DEFAULT_TEMPLATE);
            $this->page->addStylesheet(SITE_URL . 'styles/design.css');
        }

        public function index($args) {

            $this->loggedIn();

            $serviceProviders = Ndoorse_Serviceprovider::getAllProviders($args);

            $this->page->startBlock('main');
            include SITE_PATH . 'layouts/serviceproviders/list.php';
            $this->page->endBlock('main');
            $this->page->render($args);

        }

        public function profile($args) {
            $this->loggedIn();
            //pr($args);
            if(isset($args[1])) {
                $serviceProviders = array(new Ndoorse_Serviceprovider($args[1]));
            } else {
                redirect(BASE_URL . 'serviceproviders');
            }


            $this->page->startBlock('main');
            include SITE_PATH . 'layouts/serviceproviders/view.php';
            $this->page->endBlock('main');
            $this->page->render($args);
        }

        public function invite($args) {
            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->loggedIn();
                
                $inviteFormRules = ServiceProviderInviteControl::$inviteFormRules;
                $inviteFormErrors = FormValidator::validate($args, $inviteFormRules);

                //pr($inviteFormErrors);
                if((isset($args[1]) && $args[1] != 'new' ) || (isset($args['serviceproviderID']) && $args['serviceproviderID'] > 0)) {
                    if(isset($args[1])) {
                        $serviceProvider = new Ndoorse_Serviceprovider($args[1]);
                    } else {
                        $serviceProvider = new Ndoorse_Serviceprovider($args['serviceproviderID']);
                    }
                } else {
                    $serviceProvider = new Ndoorse_Serviceprovider();
                    if(!empty($args[2]) && $args[1] == 'new') {
                        $serviceProvider->name = $args[2];
                    }
                }
                
                $serviceProvider->loadAttributes();
                
                if($args['userID']) {
                    $connectTo = $args['userID'];
                    $result = $serviceProvider->inviteMember($connectTo);
                } else {
                    $connectTo = Ndoorse_Member::getUserIDByName($args['respondent']);
                    if(is_numeric($connectTo)) {
                        $result = $serviceProvider->inviteMember($connectTo);
                    }
                }
                
                if($result) {
                    $_SESSION['page_messages'][] = 'Invite sent to the memer!';
                } else {
                    $_SESSION['page_errors'][] = 'Failed invite, member not found!'; 
                }
                
                
                //pr($args,FALSE);
                //pr($_POST);
                $this->page->startBlock('main');

                include SITE_PATH . 'layouts/serviceproviders/edit.php';
                $this->page->endBlock('main');
                $this->page->render($args);
    
            } else {
                redirect(BASE_URL.'serviceproviders/post/'.$args[1].'/');
            }   
            
        }

        public function remove($args) {
            
            $this->loggedIn();
            
            $inviteFormRules = ServiceProviderInviteControl::$inviteFormRules;
            $inviteFormErrors = FormValidator::validate($args, $inviteFormRules);

            //pr($inviteFormErrors);
            if((isset($args[1]) && $args[1] != 'new' ) || (isset($args['serviceproviderID']) && $args['serviceproviderID'] > 0)) {
                if(isset($args[1])) {
                    $serviceProvider = new Ndoorse_Serviceprovider($args[1]);
                } else {
                    $serviceProvider = new Ndoorse_Serviceprovider($args['serviceproviderID']);
                }
            } else {
                $serviceProvider = new Ndoorse_Serviceprovider();
                if(!empty($args[2]) && $args[1] == 'new') {
                    $serviceProvider->name = $args[2];
                }
            }
            
            $serviceProvider->loadAttributes();
            
            if(isset($args[2]))
                $serviceProvider->ignoreInvite($args[2]);
            
            redirect(BASE_URL.'serviceproviders/post/'.$args[1].'/');
            
        } 

        public function post($args) {
            
            $inviteFormRules = ServiceProviderInviteControl::$inviteFormRules;
            $inviteFormErrors = FormValidator::validate($args, $inviteFormRules);
            
            $this->loggedIn();
            //pr($args);
            if((isset($args[1]) && $args[1] != 'new' ) || (isset($args['serviceproviderID']) && $args['serviceproviderID'] > 0)) {
                if(isset($args[1])) {
                    $serviceProvider = new Ndoorse_Serviceprovider($args[1]);
                } else {
                    $serviceProvider = new Ndoorse_Serviceprovider($args['serviceproviderID']);
                }
            } else {
                $serviceProvider = new Ndoorse_Serviceprovider();
                if(!empty($args[2]) && $args[1] == 'new') {
                    $serviceProvider->name = $args[2];
                }
            }

            $serviceProvider->loadAttributes();

            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                
                if(!empty($args['logo'])) {
                    list($success, $output) = Ndoorse_Document::upload('logo');
                } else {
                    $success = true;
                    $output = $serviceProvider->logoID;
                }
                
                if(!empty($args['brochure'])) {
                    list($success, $output2) = Ndoorse_Document::upload('brochure');
                } else {
                    $success = true;
                    $output2 = $serviceProvider->documentURL;
                }


                if($success || $output == '' || $output2 == '') { // either a file was successfully uploaded, or no file was selected
                    if(!empty($args['logo']))
                        $serviceProvider->logoID = $output;
                    
                    if(!empty($args['brochure'])) 
                        $serviceProvider->documentURL = $output2;
                    
                    $serviceProvider->loadFromArray($args);
                    $serviceProvider->loadAttributes();
                    if(isset($_POST['twitterFeed'])) {
                        $serviceProvider->twitterFeed = 1;
                    } else {
                        $serviceProvider->twitterFeed = 0;
                    }
                    
                    //pr($serviceProvider, false);
                    //pr($args);
                    
                    $serviceProvider->locationID = Ndoorse_Location::saveFromPost($args['location'], $args['locationID']);
                    $serviceProvider->status = 2;

                    if($serviceProvider->save()) {
                        Ndoorse_Member::addServiceProvider($serviceProvider->serviceproviderID);

                        if(!empty($args['industry']))
                            Ndoorse_Serviceprovider::addIndustryAttribute($serviceProvider->serviceproviderID, $args['industry']);

                        $_SESSION['page_messages'][] = 'Service Provider details saved sucssesfully.';
                        redirect(BASE_URL . 'members/profile/');
                    } else {
                        $_SESSION['page_errors'][] = 'Failed to save Service Provider details!';
                    }
                } else {
                    $_SESSION['page_errors'][] = 'There was a problem uploading your Logo: ' . $output;
                    $_SESSION['page_errors'][] = 'Failed to save Service Provider details!';
                }
            }

            $this->page->startBlock('main');

            include SITE_PATH . 'layouts/serviceproviders/edit.php';
            $this->page->endBlock('main');
            $this->page->render($args);

        }
    }
?>