<?php

namespace CultuurNet\Hydra;

class PagedCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageUrlGenerator | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageUrlGenerator;

    public function setUp()
    {
        $this->pageUrlGenerator = $this->createMock(PageUrlGenerator::class);
        $this->pageUrlGenerator
            ->expects($this->any())
            ->method('urlForPage')
            ->willReturnArgument(0);
    }

    /**
     * @test
     */
    public function it_has_getters_for_all_injected_properties()
    {
        $pageNumber = 1;
        $itemsPerPage = 10;
        $members = [
            (object) ['id' => 0],
            (object) ['id' => 1],
            (object) ['id' => 2],
            (object) ['id' => 3],
            (object) ['id' => 4],
            (object) ['id' => 5],
            (object) ['id' => 6],
            (object) ['id' => 7],
            (object) ['id' => 8],
            (object) ['id' => 9],
        ];
        $totalItems = 20;
        $usesZeroBasedNumbering = true;

        $pagedCollection = new PagedCollection(
            $pageNumber,
            $itemsPerPage,
            $members,
            $totalItems,
            $this->pageUrlGenerator,
            $usesZeroBasedNumbering
        );

        $this->assertEquals($pageNumber, $pagedCollection->getPageNumber());
        $this->assertEquals($itemsPerPage, $pagedCollection->getItemsPerPage());
        $this->assertEquals($members, $pagedCollection->getMembers());
        $this->assertEquals($totalItems, $pagedCollection->getTotalItems());
        $this->assertEquals($this->pageUrlGenerator, $pagedCollection->getPageUrlGenerator());
        $this->assertEquals($usesZeroBasedNumbering, $pagedCollection->usesZeroBasedNumbering());
    }

    /**
     * @test
     */
    public function it_can_create_a_copy_with_updated_members()
    {
        $originalMembers = [
            (object) ['id' => 0],
            (object) ['id' => 1],
            (object) ['id' => 2],
            (object) ['id' => 3],
            (object) ['id' => 4],
            (object) ['id' => 5],
            (object) ['id' => 6],
            (object) ['id' => 7],
            (object) ['id' => 8],
            (object) ['id' => 9],
        ];

        $updatedMembers = array_map(
            function (\stdClass $member) {
                $member = clone $member;
                $member->foo = 'bar';
                return $member;
            },
            $originalMembers
        );

        $originalPagedCollection = new PagedCollection(
            1,
            10,
            $originalMembers,
            20
        );

        $updatedPagedCollection = $originalPagedCollection
            ->withMembers($updatedMembers);

        $this->assertEquals($originalMembers, $originalPagedCollection->getMembers());
        $this->assertEquals($updatedMembers, $updatedPagedCollection->getMembers());
    }

    /**
     * @test
     */
    public function it_can_create_a_copy_with_an_updated_page_url_factory()
    {
        $originalPagedCollection = new PagedCollection(
            1,
            10,
            [],
            20
        );

        $updatedPagedCollection = $originalPagedCollection
            ->withPageUrlGenerator($this->pageUrlGenerator);

        $this->assertNull($originalPagedCollection->getPageUrlGenerator());
        $this->assertEquals($this->pageUrlGenerator, $updatedPagedCollection->getPageUrlGenerator());
    }

    /**
     * @test
     */
    public function it_includes_the_next_and_last_page_when_a_generator_is_set()
    {
        $pagedCollection = new PagedCollection(
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

        $firstPage = $pagedCollection->jsonSerialize();
        $this->assertEquals($expectCollection, $firstPage);
    }

    /**
     * @test
     */
    public function it_includes_the_previous_page_and_first_page_when_a_generator_is_set()
    {
        $pagedCollection = new PagedCollection(
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

        $lastPage = $pagedCollection->jsonSerialize();
        $this->assertEquals($expectCollection, $lastPage);
    }

    /**
     * @test
     */
    public function it_includes_the_next_and_last_page_when_using_zero_based_page_numbering()
    {

        $pagedCollection = new PagedCollection(
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

        $firstPage = $pagedCollection->jsonSerialize();
        $this->assertEquals($expectCollection, $firstPage);
    }

    /**
     * @test
     */
    public function it_includes_the_previous_page_when_using_zero_based_page_numbering()
    {
        $pagedCollection = new PagedCollection(
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

        $secondPage = $pagedCollection->jsonSerialize();
        $this->assertEquals($expectCollection, $secondPage);
    }

    /**
     * @test
     */
    public function it_leaves_out_page_info_when_no_page_generator_is_set()
    {
        $pagedCollection = new PagedCollection(
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

        $collection = $pagedCollection->jsonSerialize();
        $this->assertEquals($expectCollection, $collection);
    }
}
