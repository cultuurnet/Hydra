<?php
/**
 * @file
 */

namespace CultuurNet\Hydra;

class PagedCollection implements \JsonSerializable
{
    /**
     * @var int
     */
    private $pageNumber;

    /**
     * @var int
     */
    private $itemsPerPage;

    /**
     * @var array
     */
    private $members;

    /**
     * @var int
     */
    private $totalItems;

    /**
     * @var PageUrlGenerator
     */
    private $pageUrlFactory;

    /**
     * @var int
     */
    private $firstPageNumber = 1;

    /**
     * @param int $pageNumber
     * @param int $itemsPerPage
     * @param array $members
     * @param int $totalItems
     * @param PageUrlGenerator $pageUrlFactory
     */
    public function __construct(
        $pageNumber,
        $itemsPerPage,
        array $members,
        $totalItems,
        PageUrlGenerator $pageUrlFactory
    ) {
        $this->setPageNumber($pageNumber);
        $this->setItemsPerpage($itemsPerPage);
        $this->members = $members;
        $this->setTotalItems($totalItems);
        $this->pageUrlFactory = $pageUrlFactory;
    }

    private function setPageNumber($pageNumber)
    {
        if (!is_int($pageNumber)) {
            throw new \InvalidArgumentException(
                'pageNumber should be an integer, got ' . gettype($pageNumber)
            );
        }
        $this->pageNumber = $pageNumber;
    }

    /**
     * @param int $totalItems
     */
    private function setTotalItems($totalItems)
    {
        if (!is_int($totalItems)) {
            throw new \InvalidArgumentException(
                'totalItems should be an integer, got ' . gettype($totalItems)
            );
        }
        $this->totalItems = $totalItems;
    }

    /**
     * @param int $itemsPerPage
     */
    private function setItemsPerPage($itemsPerPage)
    {
        if (!is_int($itemsPerPage)) {
            throw new \InvalidArgumentException(
                'totalItems should be an integer, got ' . gettype($itemsPerPage)
            );
        }
        $this->itemsPerPage = $itemsPerPage;
    }

    public function firstPage()
    {
        return $this->pageUrlFactory->urlForPage($this->firstPageNumber);
    }

    /**
     * @return int
     */
    private function lastPageNumber()
    {
        $lastPageNumber = $this->firstPageNumber + (int) floor($this->totalItems / $this->itemsPerPage);

        return $lastPageNumber;
    }

    /**
     * @return string
     */
    public function lastPage()
    {
        return $this->pageUrlFactory->urlForPage(
            $this->lastPageNumber()
        );
    }

    /**
     * @return string|null
     */
    public function nextPage()
    {
        if ($this->lastPageNumber() > $this->pageNumber) {
            return $this->pageUrlFactory->urlForPage($this->pageNumber + 1);
        }
    }

    /**
     * @return string|null
     */
    public function previousPage()
    {
        if ($this->pageNumber > $this->firstPageNumber) {
            return $this->pageUrlFactory->urlForPage($this->pageNumber - 1);
        }
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $data = [
            '@context' => 'http://www.w3.org/ns/hydra/context.jsonld',
            '@type' => 'PagedCollection',
            'itemsPerPage' => $this->itemsPerPage,
            'totalItems' => $this->totalItems,
            'member' => $this->members,
            'firstPage' => $this->firstPage(),
            'lastPage' => $this->lastPage(),
            'previousPage' => $this->previousPage(),
            'nextPage' => $this->nextPage(),
        ];

        return array_filter($data);
    }
}
