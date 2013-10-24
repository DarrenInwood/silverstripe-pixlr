<?php


class PixlrAdmin extends LeftAndMain
{

    public static $menu_icon = 'pixlr/images/icons/admin-icon.png';
    public static $url_segment = 'pixlr';
    public static $menu_title = 'Photo editor';

    public static $new_folder = 'pixlr';
    public static $copies_folder = 'pixrloriginals';

    public static $allowed_actions = array(
        'saveimage',
        'edit',
        'exitimage'
    );

    private $_image = null;

    public function init()
    {
        $id = $this->request->param('ID');
        if ($id) {
            $this->_image = Image::get()->filter(array('ID' => $id))->first();
        }
        parent::init();
        Requirements::javascript(Director::absoluteBaseURL() . 'pixlr/javascript/pixlr.js');
    }

    public function getEditForm($id = null, $fields = null)
    {
        $fields = new FieldList();
        $fields->push(new LiteralField('frame', '<div id="frame-holder">Loading</div>'));
        $savelink = Director::absoluteBaseURL() . $this->Link('saveimage');
        $exitlink = Director::absoluteBaseURL() . $this->Link('exitimage');
        if ($this->_image) {
            $fields->push(new LiteralField('js', '<script>initPixlr("' . $savelink . '","' . $exitlink . '","' . $this->_image->ID . '","' . $this->_image->Title . '","' . $this->_image->getAbsoluteURL() . '");</script>'));
        } else {
            $fields->push(new LiteralField('js', '<script>initPixlr("' . $savelink . '", "' . $exitlink . '");</script>'));
        }

        $actions = new FieldList();
        $form = new Form($this, "EditForm", $fields, $actions);
        $form->addExtraClass('cms-edit-form center ' . $this->BaseCSSClasses());
        $this->extend('updateEditForm', $form);
        return $form;
    }

    public function exitimage()
    {
        exit('<script>parent.document.location.reload();</script>');
    }

    public function saveimage()
    {
        $src = $_REQUEST['image'];
        $type = $_REQUEST['type'];
        $title = Convert::raw2url($_REQUEST['title']);

        //url params
        $parts = parse_url($_SERVER['HTTP_REFERER']);
        $query = $parts['query'];
        $pairs = explode('&', $query);
        $getparams = array();
        foreach ($pairs as $pair) {
            $part = explode('=', $pair);
            $getparams[$part[0]] = $part[1];
        }

        $imgid = false;

        //overwrite if exists
        if (array_key_exists('id', $getparams)) {
            $id = $getparams['id'];
            $image = Image::get()->filter(array('ID' => $id))->first();
            if ($image) {
                //clear cache
                $image->clearResampledImages();
                //duplicate
                $pixrloriginals = Folder::find_or_make( PixlrAdmin::$copies_folder );
                //only make duplicate when you are not editing a pixrloriginals
                if ($image->ParentID != $pixrloriginals->ID) {
                    $originalfile = $image->getFullPath();
                    $copyfile = $pixrloriginals->getFullPath() . $image->Name;
                    copy($originalfile, $copyfile);
                }
                //overwrite existing
                file_put_contents($image->getFullPath(), file_get_contents($src));
                //sync folder
                $pixrloriginals->syncChildren();
                //clear cache duplicate
                $duplicateImage = Image::get()->filter(array(
                    'ParentID' => $pixrloriginals->ID,
                    'Name' => $image->Name
                ))->First();
                if ($duplicateImage) {
                    $duplicateImage->clearResampledImages();
                }
                $imgid = $image->ID;
            }
        }

        //create new if not overwritten
        if (!$imgid) {
            $folder = Folder::find_or_make( PixlrAdmin::$new_folder );
            //check if exists
            $exists = true;
            $count = 1;
            while ($exists) {
                $exists = Image::get()->filter(array(
                    'ParentID' => $folder->ID,
                    'Name' => $title . '.' . $type
                ))->First();
                if ($exists) {
                    $title = Convert::raw2url($_REQUEST['title']) . '-' . $count;
                    $count++;
                }
            }
            $title = $title . '.' . $type;
            //save image
            file_put_contents($folder->getFullPath() . $title, file_get_contents($src));
            //sync folder
            $folder->syncChildren();
            //find image
            $newimage = Image::get()->filter(array(
                'ParentID' => $folder->ID,
                'Name' => $title
            ))->First();
            $imgid = $newimage->ID;
        }

        //goto image
        $url = Director::absoluteBaseURL() . '/admin/assets/EditForm/field/File/item/' . $imgid . '/edit';
        exit('<script>parent.document.location = "' . $url . '";</script>');
    }

}

//type optie