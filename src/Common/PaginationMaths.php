<?php
/**
 * A Class that will do all the maths involved in Pagination
 *
 * Main parameters to feed this class via Constructor injection are:
 * @param int $recordsPerPage
 * @param int $totalRecordsAvailableCount
 * @param int $currentWebPageNumber
 *
 * USAGE Example:
 *              $paginatorLogic                 = new PaginationMaths($recordsPerPage, $recordCount, $currentPageWeAreOn);
 *              $pageOffset                     = $paginatorLogic->getOffset();
 *              $maximumPagesPossible           = $paginatorLogic->getMaximumPagesPossible();
 *              $currentWebPage                 = $paginatorLogic->getCurrentWebPageNumber();
 *              $startOfRecordForCurrentBatch   = $paginatorLogic->getRecordNumberForStartOfOffset();
 *              $endOfRecordForCurrentBatch     = $paginatorLogic->getRecordNumberForEndOfOffset();
 *
 *          Now you just have to feed those values to your dataAccessLayer
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Wak\Common;

/**
 * Class Pagination
 * @package Wak\Common
 */
class PaginationMaths
{
    private $recordsPerPage; //used for SQL limit clause - SELECT * FROM table LIMIT $offset, $recordsPerPage
    private $totalRecordsAvailable;
    private $maximumPagesPossible;
    private $offset;  //used for SQL limit clause - SELECT * FROM table LIMIT $offset, $recordsPerPage
    private $recordNumberForStartOfOffset; //to be used as in => Showing records [$startOfOffsetRecordNumber to $endOfOffsetRecordNumber] of $totalRecordsAvailable
    private $recordNumberForEndOfOffset; //to be used as in => Showing records [$startOfOffsetRecordNumber to $endOfOffsetRecordNumber] of $totalRecordsAvailable
    private $currentWebPageNumber;

    /**
     * @param int $recordsPerPage
     * @param int $totalRecordsAvailableCount
     * @param int $currentWebPageNumber
     */
    public function __construct($recordsPerPage, $totalRecordsAvailableCount, $currentWebPageNumber)
    {
        $this->recordsPerPage = $recordsPerPage;
        $this->totalRecordsAvailable = $totalRecordsAvailableCount;
        $this->doMaths($currentWebPageNumber);
    }

    /**
     * @return int
     */
    public function getMaximumPagesPossible()
    {
        return $this->maximumPagesPossible;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getRecordNumberForStartOfOffset()
    {
        return $this->recordNumberForStartOfOffset;
    }

    /**
     * @return int
     */
    public function getRecordNumberForEndOfOffset()
    {
        return $this->recordNumberForEndOfOffset;
    }

    /**
     * @return int
     */
    public function getCurrentWebPageNumber()
    {
        return $this->currentWebPageNumber;
    }


    /* ** PRIVATE METHODS *** */

    /**
     * @param int $currentWebPageNumber
     */
    private function doMaths($currentWebPageNumber)
    {
        $this->calculateMaximumPagesPossible();
        $this->currentWebPageNumber = (($currentWebPageNumber > $this->maximumPagesPossible) || ($currentWebPageNumber < 1)) ? 1 : $currentWebPageNumber;
        $this->calculateOffsets();
    }

    private function calculateMaximumPagesPossible()
    {
        if ($this->totalRecordsAvailable < 1) {
            $this->maximumPagesPossible = 0;
        }
        $this->maximumPagesPossible = ceil($this->totalRecordsAvailable / $this->recordsPerPage);
    }

    private function calculateOffsets()
    {
        $this->offset = ($this->currentWebPageNumber - 1) * $this->recordsPerPage;

        $this->recordNumberForStartOfOffset = $this->offset + 1;
        $this->recordNumberForEndOfOffset = ($this->offset + $this->recordsPerPage);

        //to cope with invalid page count request
        if ($this->recordsPerPage >= $this->totalRecordsAvailable) {
            $this->recordNumberForEndOfOffset = $this->totalRecordsAvailable;
        } elseif ($this->recordNumberForEndOfOffset >= (($this->maximumPagesPossible * $this->recordsPerPage)) ) {
            $this->recordNumberForEndOfOffset -= 1;
        }
    }
}
