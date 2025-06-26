<?php
namespace RLTSquare\AjaxShoppingCartUpdate\Plugin;

use Magento\Checkout\Block\Cart\Grid;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;

class CartItemsSort
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Sort cart items based on addition time, showing most recently added first
     *
     * @param Grid $subject
     * @param mixed $result
     * @return array
     */
    public function afterGetItems(Grid $subject, $result)
    {
        try {
            // Convert items to array if it's a collection
            $items = is_array($result) ? $result : $result->getItems();
           
            if (empty($items)) {
                return $result;
            }

            // Sort items by creation time in descending order (newest first)
            usort($items, function ($item1, $item2) {
                try {
                    $time1 = strtotime($item1->getCreatedAt());
                    $time2 = strtotime($item2->getCreatedAt());
                    
                    // If creation times are equal, use item ID as secondary sort
                    if ($time1 === $time2) {
                        return $item2->getItemId() <=> $item1->getItemId();
                    }
                    
                    return $time2 <=> $time1; // Descending order (newest first)
                } catch (\Exception $e) {
                    $this->logger->error('Error comparing cart items: ' . $e->getMessage());
                    return 0;
                }
            });

            return $items;
        } catch (\Exception $e) {
            $this->logger->error('Error sorting cart items: ' . $e->getMessage());
            return $result; // Return original result if sorting fails
        }
    }
}