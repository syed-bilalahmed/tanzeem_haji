<?php
include 'config.php';
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $topic = $_POST['topic'];
    $details = $_POST['details'];
    $lang = $_POST['lang'] ?? 'ur';

        // Check if `signatures` column exists or handle it appropriately
    // The previous implementation used implode(',', $_POST['signatures']) but there was no input for it in the HTML form previously shown.
    // I will replace that with a single signature setting string.

    $signature_setting = $_POST['signature_setting'] ?? 'sadr_only';

    // We are storing the setting in the 'signatures' column. 
    // This column was previously intended for multiple signatures (imploded list), 
    // but now we are storing a configuration key: 'sadr_only', 'all', 'members_sadr'.
    
    $stmt = $pdo->prepare("INSERT INTO notices (notice_date, topic, details, lang, signatures) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$date, $topic, $details, $lang, $signature_setting])) {
        echo "<script>alert('Notice added successfully!'); window.location.href='notices.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error adding notice.</div>";
    }
}
?>

<div class="card">
    <h2 class="section-title">نیا نوٹیفکیشن / رسید (Add New Notice)</h2>
    <form method="post">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>زبان (Language):</label>
                    <select name="lang" class="form-control" onchange="changeLang(this.value)">
                        <option value="ur">Urdu (اردو)</option>
                        <option value="en">English</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                 <div class="form-group">
                    <label>دستخط کی ترتیب (Signature Setting):</label>
                    <select name="signature_setting" class="form-control">
                        <option value="sadr_only">صرف صدر (President Only)</option>
                        <option value="sadr_naibsadr">صدر اور نائب صدر (President & VP)</option>
                        <option value="sadr_gensec">صدر اور جنرل سیکرٹری (President & General Sec)</option>
                        <option value="sadr_joint">صدر اور جوائنٹ سیکرٹری (President & Joint Sec)</option>
                        <option value="sadr_finance">صدر اور فنانس سیکرٹری (President & Finance Sec)</option>
                        <option value="sadr_info">صدر اور انفارمیشن سیکرٹری (President & Info Sec)</option>
                        <option value="sadr_members">صدر اور ممبران (President & Cabinet Table)</option>
                        <option value="sadr_gensec_members">صدر، جنرل سیکرٹری اور ممبران (President, Gen Sec & Member Table)</option>
                        <option value="sadr_finance_members">صدر، فنانس سیکرٹری اور ممبران (President, Finance Sec & Member Table)</option>
                        <option value="sadr_naibsadr_members">صدر، نائب صدر اور ممبران (President, VP & Member Table)</option>
                        <option value="sadr_joint_members">صدر، جوائنٹ سیکرٹری اور ممبران (President, Joint Sec & Member Table)</option>
                        <option value="sadr_info_members">صدر، انفارمیشن سیکرٹری اور ممبران (President, Info Sec & Member Table)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>تاریخ (Date):</label>
            <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="form-group">
            <label>عنوان (Topic/Subject):</label>
            <input type="text" name="topic" required placeholder="Enter Topic Name">
        </div>

        <div class="form-group">
            <label>تفصیل (Details):</label>
            <textarea name="details" id="editor" rows="10" placeholder="Enter full details here..."></textarea>
        </div>

        <button type="submit" class="btn btn-success">محفوظ کریں (Save)</button>
    </form>
</div>

<style>
    .ck-editor__editable_inline {
        min-height: 400px;
    }
</style>
<!-- CKEditor 5 Superbuild CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/super-build/ckeditor.js"></script>
<script>
    CKEDITOR.ClassicEditor.create(document.querySelector('#editor'), {
        language: 'en',
        toolbar: {
            items: [
                'undo', 'redo', '|', 'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript', 'removeFormat', '|',
                'alignment', '|',
                'numberedList', 'bulletedList', 'outdent', 'indent', '|',
                'link', 'insertTable', 'blockQuote', 'horizontalLine'
            ],
            shouldNotGroupWhenFull: true
        },
        htmlSupport: {
            allow: [ { name: /.*/, attributes: true, classes: true, styles: true } ]
        },
        // Explicitly remove all Premium/Commercial features to avoid license locks/errors
        removePlugins: [
            // Commercial Features (Require License)
            'ExportPdf', 'ExportWord', 'ImportWord', 'CKBox', 'CKFinder', 'EasyImage', 
            'RealTimeCollaborativeComments', 'RealTimeCollaborativeTrackChanges', 
            'RealTimeCollaborativeRevisionHistory', 'PresenceList', 'Comments', 
            'TrackChanges', 'TrackChangesData', 'RevisionHistory', 'Pagination', 
            'WProofreader', 'MathType', 'SlashCommand', 'Template', 'DocumentOutline', 
            'FormatPainter', 'TableOfContents', 'PasteFromOfficeEnhanced', 
            'CaseChange', 'AIAssistant', 'MergeFields', 'MultiLevelList'
        ],
        fontSize: {
            options: [
                9, 10, 11, 12, 13, 14, 15, 'default', 17, 18, 19, 20, 22, 24, 26, 28, 30
            ]
        }
    })
    .then(editor => {
        window.editor = editor; 
        
        window.changeLang = function(lang) {
            const dir = (lang === 'en') ? 'ltr' : 'rtl';
            const align = (lang === 'en') ? 'left' : 'right';
            editor.editing.view.change(writer => {
                writer.setAttribute('dir', dir, editor.editing.view.document.getRoot());
                writer.setStyle('text-align', align, editor.editing.view.document.getRoot());
            });
            console.log("Switched to " + lang);
        };

        const initialLang = document.querySelector('select[name="lang"]').value;
        window.changeLang(initialLang);
    })
    .catch(error => {
        console.error("CKEditor Init Error:", error);
    });
</script>

<?php include 'footer.php'; ?>
