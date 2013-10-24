<?php

class HtmlEditorField_ToolbarExtension extends DataExtension
{

    private static $_fieldadded = false;

    public function updateFieldsForFile(FieldList $fields)
    {
        $imgId = $this->owner->request->getVar('ID');
        if( !self::$_fieldadded && Image::get()->filter(array('ID' => $imgId))->first() ){
            $admin = new PixlrAdmin();
            $url = Director::absoluteBaseURL() . $admin->Link() . 'edit/' . $imgId;
            $text = _t('Pixlr.EDIT_BUTTON', 'Edit using the image editor');

            $html = '<script>function editImage(){';
            $html .= 'if(window!=window.top){';
            $html .= 'parent.window.location = "' . $url . '";';
            $html .= '}else{';
            $html .= 'window.location = "' . $url . '";';
            $html .= '}}</script>';
            $html .= '<a style="margin-bottom:10px;" class="action action ss-ui-button ui-button ui-widget ui-state-default ui-corner-all" href="javascript:editImage();">' . $text . '</a>';

            $fields->push(new LiteralField('EditLink', $html));
            self::$_fieldadded = true;
        }
    }

}