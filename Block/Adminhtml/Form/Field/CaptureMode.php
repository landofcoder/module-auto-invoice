<?php

namespace Lof\AutoInvoice\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Sales\Model\Order\Invoice;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class CaptureMode extends Select
{
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $options = [[
            'value' => Invoice::CAPTURE_OFFLINE,
            'label' => __('Offline'),
        ], [
            'value' => Invoice::CAPTURE_ONLINE,
            'label' => __('Online'),
        ]];

        $this->setOptions($options);

        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
