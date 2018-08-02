<?php

namespace Os2Display\CoreBundle\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;

trait Groupable {
  /**
   * @var ArrayCollection
   * @Groups({"api", "search", "api-bulk", "channel", "slide", "media", "screen", "campaign"})
   */
  protected $groups;

  /**
   * Returns the unique groupable resource type
   *
   * @return string
   */
  public function getGroupableType() {
    return get_class($this);
  }

  /**
   * Returns the unique groupable resource identifier
   *
   * @return string
   */
  public function getGroupableId() {
    return $this->getId();
  }

  public function setGroups(ArrayCollection $groups) {
    $this->groups = $groups;

    return $this;
  }

  /**
   * Returns the groups for this groupable entity
   *
   * @return ArrayCollection
   */
  public function getGroups() {
    $this->groups = $this->groups ?: new ArrayCollection();
    return $this->groups;
  }
}
