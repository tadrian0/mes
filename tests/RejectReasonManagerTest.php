<?php
require_once __DIR__ . "/Mocks.php";
require_once __DIR__ . "/../includes/RejectReasonManager.php";

class RejectReasonManagerTest {
    public function testGetCategoriesReturnsArray() {
        $mockPdo = new MockPDO();
        $mockStmt = new MockPDOStatement();

        $expectedCategories = [
            ['CategoryID' => 1, 'CategoryName' => 'Category A'],
            ['CategoryID' => 2, 'CategoryName' => 'Category B']
        ];

        $mockStmt->setFetchAllResult($expectedCategories);
        $mockPdo->setQueryResult($mockStmt);

        $manager = new RejectReasonManager($mockPdo);
        $categories = $manager->getCategories();

        if ($categories !== $expectedCategories) {
            throw new Exception("getCategories did not return the expected categories.");
        }

        // Assert the query was correct
        // The table name is hardcoded in RejectReasonManager.php as 'reject_category'
        $expectedQuery = "SELECT * FROM reject_category ORDER BY CategoryName";
        if ($mockPdo->getLastQuery() !== $expectedQuery) {
            throw new Exception("getCategories executed the wrong query. Expected: '$expectedQuery', Got: '{$mockPdo->getLastQuery()}'");
        }
    }

    public function testGetCategoriesEmpty() {
        $mockPdo = new MockPDO();
        $mockStmt = new MockPDOStatement();

        $expectedCategories = [];

        $mockStmt->setFetchAllResult($expectedCategories);
        $mockPdo->setQueryResult($mockStmt);

        $manager = new RejectReasonManager($mockPdo);
        $categories = $manager->getCategories();

        if ($categories !== $expectedCategories) {
            throw new Exception("getCategories did not return an empty array.");
        }
    }
}
