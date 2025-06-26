<?php
namespace Snowdog\CustomDescription\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Snowdog\CustomDescription\Api\CustomDescriptionRepositoryInterface;
use Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface;
use Snowdog\CustomDescription\Model\Resource\CustomDescription\Collection;
use Snowdog\CustomDescription\Model\Resource\CustomDescription\CollectionFactory;
use Snowdog\CustomDescription\Model\CustomDescriptionFactory;
use Snowdog\CustomDescription\Model\Resource\CustomDescription as CustomDescriptionResource;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class CustomDescriptionRepository
 * @package Snowdog\CustomDescription\Model
 * @SuppressWarnings(PHPMD.LongVariableName)
 * @SuppressWarnings(PHPMD.ShortVariableName)
 */
class CustomDescriptionRepository implements CustomDescriptionRepositoryInterface
{
    protected $entities = [];
    protected $entitiesByProductId = [];
    protected $allLoaded = false;
    protected $customDescriptionFactory;
    protected $customDescriptionCollectionFactory;
    protected $resource;

    public function __construct(
        CustomDescriptionFactory $customDescriptionFactory,
        CollectionFactory $customDescriptionCollectionFactory,
        CustomDescriptionResource $customDescriptionResource
    ) {
        $this->customDescriptionFactory = $customDescriptionFactory;
        $this->customDescriptionCollectionFactory = $customDescriptionCollectionFactory;
        $this->resource = $customDescriptionResource;
    }

    public function get($id)
    {
        if (isset($this->entities[$id])) {
            return $this->entities[$id];
        }

        $customDescription = $this->customDescriptionFactory->create();
        $customDescription->load($id);

        if (!$customDescription->getId()) {
            throw new NoSuchEntityException(__('Requested custom description is not found'));
        }

        if (
            in_array($customDescription->getExpiryStatus(), [0, 5]) &&
            in_array($customDescription->getExpiryStatusOption(), [4, 5])
        ) {
            throw new NoSuchEntityException(__('Requested custom description is not found'));
        }

        $this->entities[$id] = $customDescription;

        return $customDescription;
    }

    public function getAll()
    {
        if (!$this->allLoaded) {
            /** @var Collection $customDescriptionCollection */
            $customDescriptionCollection = $this->customDescriptionCollectionFactory->create();

            $customDescriptionCollection->addFieldToFilter('expiry_status', ['nin' => [0, 5]]);
            $customDescriptionCollection->addFieldToFilter('expiry_status_option', ['nin' => [4, 5]]);

            foreach ($customDescriptionCollection as $item) {
                $this->entities[$item->getId()] = $item;
                $this->entitiesByProductId[$item->getProductId()][] = $item;
            }

            $this->allLoaded = true;
        }

        return $this->entities;
    }

    public function getCustomDescriptionByProductId($productId)
    {
        if (isset($this->entitiesByProductId[$productId])) {
            return $this->entitiesByProductId[$productId];
        }

        $customDescriptionCollection = $this->customDescriptionCollectionFactory->create();
        $customDescriptionCollection->addFieldToFilter('product_id', $productId);
        $customDescriptionCollection->addFieldToFilter('expiry_status', ['nin' => [0, 5]]);
        $customDescriptionCollection->addFieldToFilter('expiry_status_option', ['nin' => [4, 5]]);

        $this->entitiesByProductId[$productId] = $customDescriptionCollection->getItems();

        return $this->entitiesByProductId[$productId];
    }

    public function save(CustomDescriptionInterface $customDescription)
    {
        try {
            $this->resource->save($customDescription);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $customDescription;
    }

    public function delete(CustomDescriptionInterface $customDescription)
    {
        try {
            $this->resource->delete($customDescription);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
    }

    public function getList()
    {
        $collection = $this->customDescriptionCollectionFactory->create();
        $collection->addFieldToFilter('expiry_status', ['nin' => [0, 5]]);
        $collection->addFieldToFilter('expiry_status_option', ['nin' => [4, 5]]);

        return $collection->getItems();
    }

    public function getListByProductId($productId)
    {
        $collection = $this->customDescriptionCollectionFactory->create();
        $collection->addFieldToFilter('product_id', $productId);

        $collection->addFieldToFilter('expiry_status', ['nin' => [0, 5]]);
        $collection->addFieldToFilter('expiry_status_option', ['nin' => [4, 5]]);

        return $collection->getItems();
    }
}
