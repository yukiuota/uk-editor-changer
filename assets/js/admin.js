/**
 * UK Editor Changer Admin JavaScript
 */

jQuery(document).ready(function ($) {

    // 設定フォームの強化
    var $form = $('form[action="options.php"]');
    var $submitButton = $form.find('input[type="submit"]');
    var originalButtonText = $submitButton.val();

    // フォーム送信時の処理
    $form.on('submit', function () {
        $submitButton.val('保存中...').prop('disabled', true);
    });

    // エディタ選択の変更を監視
    $('select[name^="uk_editor_changer_options"]').on('change', function () {
        var $this = $(this);
        var $row = $this.closest('tr');
        var selectedValue = $this.val();

        // 視覚的フィードバックを追加
        $row.addClass('highlight-change');
        setTimeout(function () {
            $row.removeClass('highlight-change');
        }, 1000);

        // エディタタイプに応じたアイコンを表示
        updateEditorIcon($this, selectedValue);
    });

    // 初期化時にアイコンを設定
    $('select[name^="uk_editor_changer_options"]').each(function () {
        var $this = $(this);
        var selectedValue = $this.val();
        updateEditorIcon($this, selectedValue);
    });

    /**
     * エディタタイプのアイコンを更新
     */
    function updateEditorIcon($select, editorType) {
        var $icon = $select.siblings('.editor-type-icon');

        if ($icon.length === 0) {
            $icon = $('<span class="editor-type-icon"></span>');
            $select.before($icon);
        }

        $icon.removeClass('editor-type-gutenberg editor-type-classic');

        if (editorType === 'gutenberg') {
            $icon.addClass('editor-type-gutenberg').attr('title', 'Gutenbergエディタ');
        } else {
            $icon.addClass('editor-type-classic').attr('title', 'クラシックエディタ');
        }
    }

    // 設定の一括変更機能
    if ($('select[name^="uk_editor_changer_options"]').length > 1) {
        var $bulkControls = $('<div class="uk-bulk-controls"></div>');
        $bulkControls.html(
            '<h3>一括設定</h3>' +
            '<p>' +
            '<button type="button" class="button" data-editor="gutenberg">すべてGutenbergに設定</button> ' +
            '<button type="button" class="button" data-editor="classic">すべてクラシックエディタに設定</button>' +
            '</p>'
        );

        $form.prepend($bulkControls);

        // 一括設定ボタンのイベント
        $('.uk-bulk-controls button').on('click', function () {
            var editorType = $(this).data('editor');
            var confirmed = confirm('すべての投稿タイプのエディタを「' +
                (editorType === 'gutenberg' ? 'Gutenberg' : 'クラシックエディタ') +
                '」に変更しますか？');

            if (confirmed) {
                $('select[name^="uk_editor_changer_options"]').val(editorType).trigger('change');
            }
        });
    }

    // 設定変更の警告
    var originalValues = {};
    $('select[name^="uk_editor_changer_options"]').each(function () {
        originalValues[this.name] = this.value;
    });

    var hasChanges = false;

    $('select[name^="uk_editor_changer_options"]').on('change', function () {
        hasChanges = false;
        $('select[name^="uk_editor_changer_options"]').each(function () {
            if (originalValues[this.name] !== this.value) {
                hasChanges = true;
                return false;
            }
        });

        if (hasChanges) {
            $submitButton.addClass('button-primary');
        } else {
            $submitButton.removeClass('button-primary');
        }
    });

    // ページを離れる前の警告
    $(window).on('beforeunload', function () {
        if (hasChanges) {
            return '設定が保存されていません。このページを離れますか？';
        }
    });

    // フォーム送信時は警告を無効化
    $form.on('submit', function () {
        hasChanges = false;
    });

});

// CSS for dynamic elements
var dynamicCSS = `
    .highlight-change {
        background-color: #fff3cd !important;
        transition: background-color 1s ease;
    }
    
    .uk-bulk-controls {
        background: #f0f0f1;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        border-left: 4px solid #72aee6;
    }
    
    .uk-bulk-controls h3 {
        margin-top: 0;
        margin-bottom: 10px;
    }
    
    .uk-bulk-controls p {
        margin-bottom: 0;
    }
    
    .uk-bulk-controls button {
        margin-right: 10px;
    }
`;

// 動的CSSを追加
if (document.head) {
    var style = document.createElement('style');
    style.textContent = dynamicCSS;
    document.head.appendChild(style);
}