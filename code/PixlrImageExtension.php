<?php

class PixlrImageExtension extends DataExtension
{

    public function updateCMSFields(FieldList $fields)
    {
        $admin = new PixlrAdmin();
        $url = Director::absoluteBaseURL() . $admin->Link() . 'edit/' . $this->owner->ID;
        $text = _t('Pixlr.EDIT_BUTTON', 'Edit using the image editor');

        $html = '<script>function editImage(){';
        $html .= 'if(window!=window.top){';
        $html .= 'parent.window.location = "' . $url . '";';
        $html .= '}else{';
        $html .= 'window.location = "' . $url . '";';
        $html .= '}}</script>';
        $html .= '<a style="margin-bottom:10px;" class="action action ss-ui-button ui-button ui-widget ui-state-default ui-corner-all" href="javascript:editImage();">' . $text . '</a>';

        $fields->addFieldToTab('Root.Main', new LiteralField('EditLink', $html));
    }

    public function clearResampledImages()  {
        $files = glob(Director::baseFolder().'/'.$this->owner->Parent()->Filename."_resampled/*-".$this->owner->Name);
        foreach($files as $file) {unlink($file);}
    }

}