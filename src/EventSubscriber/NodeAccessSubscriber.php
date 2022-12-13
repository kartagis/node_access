<?php

namespace Drupal\node_access\EventSubscriber;

use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class NodeAccessSubscriber implements EventSubscriberInterface {

  /**
   * Current user account.
   * @var Drupal\Core\Session\AccountInterface
   */
  protected $current_user;

  /**
   * HajansNodeAccessSubscriber constructor.
   * @param \Drupal\Core\Session\AccountInterface
   * Current user account.
   */
  public function __construct(AccountInterface $current_user) {
    $user = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => [['denyAccessForRestrictedNodes', 0]]];
  }

  /**
   * Spits out information about the current node.
   */
  public function spitOutInfo(GetResponseEvent $event) {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {
      $nid = $node->id();
      
    }
  }
  /**
   * Deny access to restricted node content.
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   * Kernel event
   *
   * @throw \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   * When user does not have access to the node.
   */
  public function denyAccessForRestrictedNodes(GetResponseEvent $event) {
    $user = User::load(\Drupal::currentUser()->id());
    $this->messenger = \Drupal::messenger();
    $route_match = RouteMatch::createFromRequest($event->getRequest());
    if (($node = $route_match->getParameter('node')) && $node instanceof NodeInterface) {
      if ($node->bundle() == 'stream') {
        if ($user->hasRole('super_admin')) {
          if ($node->field_to->value !== 'Everyone' || $node->field_to->value !== 'Myself') {
            $to = $node->field_specific_person->target_id;
            $to = User::load($to);
            $name = $to->getAccountName();
            if ($user->getAccountName() != $name) {
              throw new AccessDeniedHttpException('You are not authorised to view this stream');
            }
          }

        }
      }
    }

  }
}
