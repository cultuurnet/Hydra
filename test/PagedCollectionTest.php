<?php

namespace CultuurNet\Hydra;

class PagedCollectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PagedCollection
     */
    protected $pagedCollection;

    /**
     * @var PageUrlGenerator | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageUrlGenerator;

    public function setUp()
    {
        $this->pageUrlGenerator = $this->getMock(PageUrlGenerator::class);
        $this->pageUrlGenerator
            ->expects($this->any())
            ->method('urlForPage')
            ->willReturnArgument(0);
    }

    /**
     * @test
     */
    public function it_includes_the_next_and_last_page_when_a_generator_is_set()
    {
        $this->pagedCollection = new PagedCollection(
            1,
            1,
            ['item one'],
            2,
            $this->pageUrlGenerator
        );

        $expectCollection = [
            '@context' => 'http://www.w3.org/ns/hydra/context.jsonld',
            '@type' => 'PagedCollection',
            'itemsPerPage' => 1,
            'totalItems' => 2,
            'member' => ['item one'],
            'firstPage' => 1,
            'lastPage' => 2,
            'nextPage' => 2
        ];

        $firstPage = $this->pagedCollection->jsonSerialize();
        $this->assertEquals($expectCollection, $firstPage);
    }

    /**
     * @test
     */
    public function it_includes_the_previous_page_and_first_page_when_a_generator_is_set()
    {
        $this->pagedCollection = new PagedCollection(
            2,
            1,
            ['item two'],
            2,
            $this->pageUrlGenerator
        );

        $expectCollection = [
            '@context' => 'http://www.w3.org/ns/hydra/context.jsonld',
            '@type' => 'PagedCollection',
            'itemsPerPage' => 1,
            'totalItems' => 2,
            'member' => ['item two'],
            'firstPage' => 1,
            'lastPage' => 2,
            'previousPage' => 1
        ];

        $lastPage = $this->pagedCollection->jsonSerialize();
        $this->assertEquals($expectCollection, $lastPage);
    }

    /**
     * @test
     */
    public function it_includes_the_next_and_last_page_when_using_zero_based_page_numbering()
    {

        $this->pagedCollection = new PagedCollection(
            0,
            1,
            ['item one'],
            2,
            $this->pageUrlGenerator,
            true
        );

        $expectCollection = [
            '@context' => 'http://www.w3.org/ns/hydra/context.jsonld',
            '@type' => 'PagedCollection',
            'itemsPerPage' => 1,
            'totalItems' => 2,
            'member' => ['item one'],
            'firstPage' => 0,
            'lastPage' => 1,
            'nextPage' => 1
        ];

        $firstPage = $this->pagedCollection->jsonSerialize();
        $this->assertEquals($expectCollection, $firstPage);
    }

    /**
     * @test
     */
    public function it_includes_the_previous_page_when_using_zero_based_page_numbering()
    {
        $this->pagedCollection = new PagedCollection(
            1,
            1,
            ['item two'],
            2,
            $this->pageUrlGenerator,
            true
        );

        $expectCollection = [
            '@context' => 'http://www.w3.org/ns/hydra/context.jsonld',
            '@type' => 'PagedCollection',
            'itemsPerPage' => 1,
            'totalItems' => 2,
            'member' => ['item two'],
            'firstPage' => 0,
            'lastPage' => 1,
            'previousPage' => 0
        ];

        $secondPage = $this->pagedCollection->jsonSerialize();
        $this->assertEquals($expectCollection, $secondPage);
    }

    /**
     * @test
     */
    public function it_leaves_out_page_info_when_no_page_generator_is_set()
    {
        $this->pagedCollection = new PagedCollection(
            1,
            1,
            ['item two'],
            2
        );

        $expectCollection = [
            '@context' => 'http://www.w3.org/ns/hydra/context.jsonld',
            '@type' => 'PagedCollection',
            'itemsPerPage' => 1,
            'totalItems' => 2,
            'member' => ['item two']
        ];

        $collection = $this->pagedCollection->jsonSerialize();
        $this->assertEquals($expectCollection, $collection);
    }
}
