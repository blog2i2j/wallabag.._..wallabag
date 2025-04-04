<?php

namespace Wallabag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\XmlRoot;
use Wallabag\Repository\TagRepository;

/**
 * Tag.
 *
 * @XmlRoot("tag")
 * @ExclusionPolicy("all")
 */
#[ORM\Table(name: '`tag`')]
#[ORM\Index(columns: ['label'])]
#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    /**
     * @var int
     *
     * @Expose
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     *
     * @Expose
     */
    #[ORM\Column(name: 'label', type: 'text')]
    private $label;

    /**
     * @Expose
     * @Gedmo\Slug(fields={"label"}, prefix="t:")
     */
    #[ORM\Column(length: 128, unique: true)]
    private $slug;

    #[ORM\ManyToMany(targetEntity: Entry::class, mappedBy: 'tags', cascade: ['persist'])]
    private $entries;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->label;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set label.
     *
     * @param string $label
     *
     * @return Tag
     */
    public function setLabel($label)
    {
        $this->label = mb_convert_case($label, \MB_CASE_LOWER);

        return $this;
    }

    /**
     * Get label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function addEntry(Entry $entry)
    {
        if ($this->entries->contains($entry)) {
            return;
        }

        $this->entries->add($entry);
        $entry->addTag($this);
    }

    public function removeEntry(Entry $entry)
    {
        if (!$this->entries->contains($entry)) {
            return;
        }

        $this->entries->removeElement($entry);
        $entry->removeTag($this);
    }

    public function hasEntry($entry)
    {
        return $this->entries->contains($entry);
    }

    /**
     * Get entries for this tag.
     *
     * @return ArrayCollection<Entry>
     */
    public function getEntries()
    {
        return $this->entries;
    }

    public function getEntriesByUserId($userId)
    {
        $filteredEntries = new ArrayCollection();
        foreach ($this->entries as $entry) {
            if ($entry->getUser()->getId() === $userId) {
                $filteredEntries->add($entry);
            }
        }

        return $filteredEntries;
    }
}
