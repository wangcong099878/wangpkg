<?php

namespace Wang\Pkg\Extensions\Form;

use Encore\Admin\Form\Field;

class CKEditor extends Field
{
    public static $js = [
        'vendor/wangpkg/lib/ckeditor5/build/ckeditor.js',
        //'/packages/ckeditor/adapters/jquery.js',
    ];

    protected $view = 'wangpkg::admin.form.ckeditor';

    public function render()
    {
        //$this->script = "$('textarea.{$this->getElementClassString()}').ckeditor();";
        $this->script = <<<EOT

ClassicEditor
        .create(document.querySelector('textarea.{$this->getElementClassString()}'), {
            toolbar: {
                items: [
                    'heading',
                    '|',
                    'bold',
                    'imageUpload',
                    'alignment',
                    'bulletedList',
                    'numberedList',
                    //'todoList',
                    'removeFormat',
                ]
            },
            ckfinder: {
                uploadUrl: '/wangpkg/ckUpload'
            },
            language: 'zh-cn',
            image: {
                toolbar: [
                    'imageTextAlternative',
                    'imageStyle:alignLeft',
                    'imageStyle:alignCenter',
                    'imageStyle:alignRight',
                    'imageStyle:side',
                    'imageStyle:full',
                ],
                styles: [
                    'alignLeft',
                    'alignCenter',
                    'alignRight',
                    'side',
                    'full'
                ]
            },
            table: {
                contentToolbar: [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells'
                ]
            },
            licenseKey: '',

        })
        .then(editor => {
            window.editor = editor;

/*            const doc = editor.model.document;

            // 远程图片上传
            // 参考 https://github.com/ckeditor/ckeditor5-image/blob/master/src/imageupload/imageuploadediting.js#L78
            editor.model.document.on('change', () => {

                const changes = doc.differ.getChanges();

                for (const entry of changes) {
                    if (entry.type === 'insert' && entry.name === 'image') {
                        console.log('开始上传图片了');
                        const item = entry.position.nodeAfter;

                        // Check if the image element still has upload id.
                        const uploadId = item.getAttribute('uploadId');
                        const uploadStatus = item.getAttribute('uploadStatus');

                        console.log(uploadId);
                        console.log(uploadStatus);
                        if (!uploadId && !uploadStatus) {
                            console.log('图片上传结束');
                            //CatchRemoteImage(editor, item);
                        }
                    }
                }
            });*/
        })
        .catch(error => {
            console.error('Oops, something gone wrong!');
            console.error('Please, report the following error in the https://github.com/ckeditor/ckeditor5 with the build id and the error stack trace:');
            console.warn('Build id: gvv38cz3ln8r-8o65j7c6blw0');
            console.error(error);
        });
EOT;

        return parent::render();
    }
}
