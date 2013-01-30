<?php
	class EventsController extends Controller {

		public function __construct() {
			parent::__construct(SITE_NAME . ' Events', defined('SITE_TEMPLATE') ? SITE_TEMPLATE : DEFAULT_TEMPLATE);
			$this->page->addStylesheet(SITE_URL . 'styles/design.css');
			$this->page->addStylesheet(SITE_URL . 'styles/events.css');
			$this->page->addStylesheet(SITE_URL . 'styles/jquery-ui-1.9.1.custom.min.css');
		}

		public function index($args) {

			if(isset($args['month']) && $args['month'] > 0 && $args['month'] < 13) {
				$m = (int)$args['month'];
			} else {
				$m = date('m');
			}

			if(isset($args['year'])) {
				if($args['year'] >= date('Y') && $args['year'] < date('Y') + 2) {
					$y = (int)$args['year'];
				}
				if($args['year'] >= date('Y') + 2 || $args['year'] < date('Y')) {
					$m = date('m');
				}
			} else {
				$y = date('Y');
			}

			$start = mktime(0,0,0,$m,1,$y);
			$end = mktime(23,59,59,$m,date('t', mktime(0,0,0,$m,1,$y)), $y);

			$startDay = date('N', $start);
			$endDay = date('N', $end);

			if($startDay == 1) {
				$startDate = new DateTime('@' . $start);
			} else {
				$startDate = new DateTime('@' . strtotime((-1 * (1 - $startDay)) . ' days ago',  $start));
			}

			if($endDay == 7) {
				$endDate = new DateTime('@' . $end);
			} else {
				$endDate = new DateTime('@' . strtotime('+' . ((7 - $endDay)) . ' days', $end));
			}

			$startDate->setTimezone(new DateTimeZone('Europe/London'));
			$endDate->setTimezone(new DateTimeZone('Europe/London'));

			$oneday = new DateInterval('P1D');
			$month = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);

			$events = Ndoorse_Event::getEvents($m, $y);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/events/calendar.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function post($args) {

			if(isset($args[1])) {
				$event = new Ndoorse_Event($args[1]);
				if(!$event->getID()) {
					$_SESSION['page_errors'][] = 'Sorry, the event you specified could not be found.';
					redirect(BASE_URL . 'events/');
				}
				if($event->userID != $_SESSION['user']->getID()) {
					$_SESSION['page_errors'][] = 'Sorry, you do not have permission to edit this event.';
					redirect(BASE_URL . 'events/');
				}

			} else {
				$event = new Ndoorse_Event();
			}

			if($_SERVER['REQUEST_METHOD'] == 'POST') {

				$fieldErrors = FormValidator::validate($args, Ndoorse_Event::$eventRules);
				$event->loadFromArray($args);
				$event->startDate = $args['startDate'] . (' ' . $args['startTime_hour'] . ':' . $args['startTime_minute'] . ':00');
				$event->endDate = $args['endDate'] . (' ' . $args['endTime_hour'] . ':' . $args['endTime_minute'] . ':00');

				if(empty($fieldErrors)) {

					$event->userID = $_SESSION['user']->getID();
					$event->status = Ndoorse_Event::STATUS_PENDING;

					$event->locationID = Ndoorse_Location::saveFromPost($args['location'], $args['locationID']);

					if($event->save()) {
						$tickets = array();
						if(isset($args['ticket_name'])) {
							foreach($args['ticket_name'] as $key=>$name) {
								if(!empty($name)) {
									if(isset($args['ticket_id']) && array_key_exists($key, $args['ticket_id'])) {
										$id = $args['ticket_id'][$key];
										$tickets[$id] = array('name'=>$name, 'id'=>$id);
										if(isset($args['ticket_price']) && array_key_exists($key, $args['ticket_price'])) {
											$tickets[$id]['price'] = $args['ticket_price'][$key];
										}
									} else {
										$price = isset($args['ticket_price']) && array_key_exists($key, $args['ticket_price']) ? $args['ticket_price'][$key] : 0;
										$tickets[] = array('name'=>$name, 'price'=>$price);
									}
								}
							}
						}
						if(!$event->saveTicketTypes($tickets)) {
							$_SESSION['page_errors'][] = 'Sorry, there was a problem saving your ticket options.';
						}

						$_SESSION['page_messages'][] = 'Your event has been submitted. You will be notified when it has been confirmed.';
						redirect(BASE_URL . 'events/');
					}

				}

			}

			if($event->getID()) {
				$tickets = $event->getTicketTypes();
			} else {
				$tickets = array();
			}

			$rules = Ndoorse_Event::getRepeatOptions();

			$form = new FormControl(BASE_URL . 'events/post/' . ($event->getID() ? $event->getID() . '/' : ''), 'event');
			$form->html('<div class="col col2 left-col"><div class="box"><h3>Event Information</h3>');
			$form->textbox('title', 'Title:', $event->title);
			$form->textarea('details', 'Event Details:', $event->details);
			$form->textbox('startDate', 'Start date:', $event->getStartDay());
			$form->hidden('startDateVal', $event->startDate);
			$form->timepicker('startTime', 'Start time:', $event->getStartTime());
			$form->textbox('endDate', 'End date:', $event->getEndDay());
			$form->hidden('endDateVal', $event->endDate);
			$form->timepicker('endTime', 'End time:', $event->getEndTime());
			$form->textbox('location', 'Location:', $event->location);
			$form->hidden('locationID', $event->locationID);
			$form->select('repeatRule', 'Repeats:', $rules);
			$form->checkbox('private', 'Private event', $event->private, 1);
			$form->html('<div class="buttonbar">');
			$form->submit('save', 'Save');
			$form->html('<a class="button" href="' . BASE_URL . 'events/"><span>Cancel</span></a>');
			$form->html('</div>');
			$form->html('</div></div>');

			$form->html('<div class="col col1"><div id="tickets" class="box"><h3>Ticketing</h3>');
			if(empty($tickets)) {
				$form->html('<span id="no-tickets">No ticketing</span>');
			} else {
				$form->html('<span id="no-tickets" style="display: none;">No ticketing</span>');
			}

			$form->textbox('ticketURL', 'Ticket URL:', $event->ticketURL);
			$form->html('<hr />');

			foreach($tickets as $ticket) {
				$form->html('<div>');
				if($ticket['ticketID'] > 0) {
					$form->hidden('ticket_id_' . $ticket['ticketID'], $ticket['ticketID'], array('name'=>'ticket_id[' . $ticket['ticketID'] . ']'));
				}
				$form->textbox('ticket_name_' . $ticket['ticketID'], 'Ticket Name:', $ticket['name'], array('name'=>'ticket_name[' . $ticket['ticketID'] . ']', 'class'=>'ticket-name'));
				$form->textbox('ticket_price_' . $ticket['ticketID'], 'Price: (optional)', $ticket['price'], array('name'=>'ticket_price[' . $ticket['ticketID'] . ']'));
				$form->button('ticket_remove_' . $ticket['ticketID'], 'Remove', 'button', false, '', array('name'=>'ticket_remove[' . $ticket['ticketID'] . ']'));
				$form->html('</div>');
			}
			$form->button('add_ticket', 'Add Ticket Type');

			$form->html('</div></div>');

			if(isset($fieldErrors)) {
				$form->setFieldErrors($fieldErrors);
			}

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/events/post.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function view($args) {

			if(!isset($args[1]) || empty($args[1])) {
				$_SESSION['page_errors'][] = 'Sorry, the event you requested was not found.';
				redirect(BASE_URL . 'events/');
			}

			$event = Ndoorse_Event::loadEvent($args[1]);
			if(!$event instanceof Ndoorse_Event) {
				if($event == Ndoorse_Event::ERR_LOAD_NOTFOUND) {
					$_SESSION['page_errors'][] = 'Sorry, the event you requested was not found.';
				} else {
					$_SESSION['page_errors'][] = 'Sorry, you do not have permission to view this event.';
				}
				redirect(BASE_URL . 'events/');
			}

			$startDate = strtotime($event->startDate);
			$endDate = strtotime($event->endDate);

			$tickets = $event->getTicketTypes();

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/events/view.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function invite($args) {

			if(!isset($args[1]) || empty($args[1])) {
				$_SESSION['page_errors'][] = 'Sorry, the event you requested was not found.';
				redirect(BASE_URL . 'events/');
			}

			$event = Ndoorse_Event::loadEvent($args[1]);
			if(!$event instanceof Ndoorse_Event) {
				if($event == Ndoorse_Event::ERR_LOAD_NOTFOUND) {
					$_SESSION['page_errors'][] = 'Sorry, the event you requested was not found.';
				} else {
					$_SESSION['page_errors'][] = 'Sorry, you do not have permission to view this event.';
				}
				redirect(BASE_URL . 'events/');
			}
			$fieldErrors = array();

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				if(isset($args['recommend_personType']) && ($args['recommend_personType'] == 'network' || $args['recommend_personType'] == 'remote')) {
					$type = $args['recommend_personType'];
				} else if(isset($args['recommend_email']) && !empty($args['recommend_email'])) {
					$type = 'external';
				} else {
					$type = 'network';
				}

				if($type == 'network') {
					$fieldErrors = FormValidator::validate($args, RecommendationControl::$internalRules);
				} else {
					$fieldErrors = FormValidator::validate($args, RecommendationControl::$externalRules);
				}

				if(empty($fieldErrors)) {

                    $recommendation = new Ndoorse_Recommendation();
                    $recommendation->entity = "event";
                    $recommendation->entityID = $event->getID();
                    $recommendation->referrerID = $_SESSION['user']->getID();

                    $subject = $_SESSION['user']->getName() . ' thinks you might be interested in this event';
                    $params = array('name'=>$_SESSION['user']->getName(),
                                    'url'=>BASE_URL,
                                    'eventtitle'=>$event->title,
                                    'company'=>$event->company,
                                    'location'=>$event->location,
                                    'enddate'=>$event->endDate,
                                    'startdate'=>$event->startDate
                                    );


                    if($type == 'remote') {
                        // check if existing member
                        $member = Ndoorse_Member::getUserByEmail($args['recommend_email']);
                        // if we have an existing member, we actually want to send an internal message
                        if($member !== false) {
                            $_SESSION['page_messages'][] = 'This person is already an ndoorse member';
                            $type = 'network';
                            // clear bits we don't need any more
                            $recommendation->applicantID = $member->userID;
                            $recommendation->email = '';
                        // otherwise we should send an email
                        } else {
                            //send and invitation with job details
                            $template = 'event_recommendation';
                            $phantomUserID = $_SESSION['user']->invite($args['recommend_email'], $template, $params, $subject, false);
                            $recommendation->applicantID = $phantomUserID;
                            $args['recommend_userID'] = $phantomUserID;
                            //continue with normal message anyways
                            $type = 'network';
                        }

                    }

                    if($type == 'network') {
                        $message = new Ndoorse_Message();
                        $message->loadTemplate('event_recommend', $params);
                        $message->senderID = $_SESSION['user'];
                        $message->subject = 'Event Recommendation';
                        $message->type = Ndoorse_Message::TYPE_INVITE;
                        $message->data = $event->getID();
                        $message->send(array($args['recommend_userID']));
                    }

                    $recommendation->save();

                    $_SESSION['page_messages'][] = 'Your invitation has been sent';

                    redirect(BASE_URL . 'events/view/' . $event->getID() . '/');

                } else {
                    $_SESSION['page_errors'][] = 'Please check the highlighted fields';
                }
            }

			$recommendForm = RecommendationControl::render($event->getID(), 'events', $fieldErrors);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/events/recommend.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

	}
?>