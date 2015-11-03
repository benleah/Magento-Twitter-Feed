<?php
class Bl_Twitterfeed_Model_System_Config_UserValidator extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        if ($this->isValueChanged()) {
            $value = $this->getValue();
            if ($value[0] == '@') {
                $message = 'Oops! Please leave off the @ in your Twitter username';
                Mage::throwException($message);
            }
        }
        return $this;
    }
}
