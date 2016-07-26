<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext;

class Attribute extends AbstractPlugin
{
    /**
     * @var \Magento\Framework\Search\Request\Config
     */
    private $config;

    /**
     * @var boolean
     */
    private $deleteNeedInvalidation;

    /**
     * @var boolean
     */
    private $saveNeedInvalidation;

    /**
     * @var boolean
     */
    private $saveIsNew;


    /**
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Framework\Search\Request\Config $config
     */
    public function __construct(
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\Search\Request\Config $config
    ) {
        parent::__construct($indexerRegistry); // TODO: Change the autogenerated stub
        $this->config = $config;
    }

    /**
     * Check for needed indexer invalidation on attribute save (searchable flag change)
     *
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $subject
     * @param \Magento\Framework\Model\AbstractModel $attribute
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Catalog\Model\ResourceModel\Attribute $subject,
        \Magento\Framework\Model\AbstractModel $attribute
    ) {
        $this->saveIsNew = $attribute->isObjectNew();
        $this->saveNeedInvalidation = (
                $attribute->dataHasChangedFor('is_searchable')
                || $attribute->dataHasChangedFor('is_filterable')
                || $attribute->dataHasChangedFor('is_visible_in_advanced_search')
            ) && ! $this->saveIsNew;
        return [$attribute];
    }

    /**
     * Invalidate indexer on attribute save (searchable flag change)
     *
     * @param \Magento\Framework\Model\AbstractModel $subject
     * @param \Magento\Framework\Model\AbstractModel $result
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Framework\Model\AbstractModel $subject,
        \Magento\Framework\Model\AbstractModel $result
    ) {
        if ($this->saveNeedInvalidation) {
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }
        if ($this->saveIsNew || $this->saveNeedInvalidation) {
            $this->config->reset();
        }

        return $result;
    }

    /**
     * Check for needed indexer invalidation on searchable attribute delete
     *
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $subject
     * @param \Magento\Framework\Model\AbstractModel $attribute
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDelete(
        \Magento\Catalog\Model\ResourceModel\Attribute $subject,
        \Magento\Framework\Model\AbstractModel $attribute
    ) {
        $this->deleteNeedInvalidation = !$attribute->isObjectNew() && $attribute->getIsSearchable();
        return [$attribute];
    }

    /**
     * Invalidate indexer on searchable attribute delete
     *
     * @param \Magento\Framework\Model\AbstractModel $subject
     * @param \Magento\Framework\Model\AbstractModel $result
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        \Magento\Framework\Model\AbstractModel $subject,
        \Magento\Framework\Model\AbstractModel $result
    ) {
        if ($this->deleteNeedInvalidation) {
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }
        return $result;
    }
}
