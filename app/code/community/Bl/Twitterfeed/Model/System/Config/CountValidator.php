<?php
class Bl_Twitterfeed_Model_System_Config_CountValidator extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        if ($this->isValueChanged()) {
            $value = $this->getValue();
            if ($value != '' && (int)$value <= 0) {
                $message = 'Oops! Please make sure the number of tweets is set to greater than 0';
                Mage::throwException($message);
            }
        }
        return $this;
    }
}
