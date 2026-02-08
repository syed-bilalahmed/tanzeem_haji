<?php
include 'config.php';
include 'header.php';

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM notices WHERE id = ?");
$stmt->execute([$id]);
$notice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$notice) {
    die("Notice not found");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $topic = $_POST['topic'];
    $details = $_POST['details'];
    $lang = $_POST['lang'];

    // Signature logic
    $signature_setting = $_POST['signature_setting'] ?? 'sadr_only';

    $update_stmt = $pdo->prepare("UPDATE notices SET notice_date = ?, topic = ?, details = ?, lang = ?, signatures = ? WHERE id = ?");
    if ($update_stmt->execute([$date, $topic, $details, $lang, $signature_setting, $id])) {
        echo "<script>alert('Notice updated successfully!'); window.location.href='notices.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error updating notice.</div>";
    }
}
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="section-title">ترمیم نوٹیفکیشن (Edit Notice)</h2>
        <a href="notices.php" class="btn btn-secondary">واپس (Back)</a>
    </div>
    
    <form method="post">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>زبان (Language):</label>
                    <select name="lang" class="form-control" onchange="changeLang(this.value)">
                        <option value="ur" <?php echo ($notice['lang']??'ur')=='ur'?'selected':''; ?>>Urdu (اردو)</option>
                        <option value="en" <?php echo ($notice['lang']??'ur')=='en'?'selected':''; ?>>English</option>
                    </select>
                </div>
            </div>
             <div class="col-md-6">
                 <div class="form-group">
                    <label>دستخط کی ترتیب (Signature Setting):</label>
                    <select name="signature_setting" class="form-control">
                        <option value="sadr_only" <?php echo ($notice['signatures']??'')=='sadr_only'?'selected':''; ?>>صرف صدر (President Only)</option>
                        <option value="sadr_naibsadr" <?php echo ($notice['signatures']??'')=='sadr_naibsadr'?'selected':''; ?>>صدر اور نائب صدر (President & VP)</option>
                        <option value="sadr_gensec" <?php echo ($notice['signatures']??'')=='sadr_gensec'?'selected':''; ?>>صدر اور جنرل سیکرٹری (President & General Sec)</option>
                        <option value="sadr_joint" <?php echo ($notice['signatures']??'')=='sadr_joint'?'selected':''; ?>>صدر اور جوائنٹ سیکرٹری (President & Joint Sec)</option>
                        <option value="sadr_finance" <?php echo ($notice['signatures']??'')=='sadr_finance'?'selected':''; ?>>صدر اور فنانس سیکرٹری (President & Finance Sec)</option>
                         <option value="sadr_info" <?php echo ($notice['signatures']??'')=='sadr_info'?'selected':''; ?>>صدر اور انفارمیشن سیکرٹری (President & Info Sec)</option>
                        <option value="sadr_members" <?php echo ($notice['signatures']??'')=='sadr_members'?'selected':''; ?>>صدر اور ممبران (President & Cabinet Table)</option>
                        <option value="sadr_gensec_members" <?php echo ($notice['signatures']??'')=='sadr_gensec_members'?'selected':''; ?>>صدر، جنرل سیکرٹری اور ممبران (President, Gen Sec & Member Table)</option>
                        <option value="sadr_finance_members" <?php echo ($notice['signatures']??'')=='sadr_finance_members'?'selected':''; ?>>صدر، فنانس سیکرٹری اور ممبران (President, Finance Sec & Member Table)</option>
                        <option value="sadr_naibsadr_members" <?php echo ($notice['signatures']??'')=='sadr_naibsadr_members'?'selected':''; ?>>صدر، نائب صدر اور ممبران (President, VP & Member Table)</option>
                        <option value="sadr_joint_members" <?php echo ($notice['signatures']??'')=='sadr_joint_members'?'selected':''; ?>>صدر، جوائنٹ سیکرٹری اور ممبران (President, Joint Sec & Member Table)</option>
                        <option value="sadr_info_members" <?php echo ($notice['signatures']??'')=='sadr_info_members'?'selected':''; ?>>صدر، انفارمیشن سیکرٹری اور ممبران (President, Info Sec & Member Table)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>تاریخ (Date):</label>
            <input type="date" name="date" required value="<?php echo $notice['notice_date']; ?>">
        </div>

        <div class="form-group">
            <label>عنوان (Topic/Subject):</label>
            <input type="text" name="topic" required value="<?php echo htmlspecialchars($notice['topic']); ?>">
        </div>

        <div class="form-group">
            <label>تفصیل (Details):</label>
            <textarea name="details" id="editor" rows="10"><?php echo htmlspecialchars($notice['details']); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">تازہ ترین کریں (Update)</button>
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
        };

        const initialLang = document.querySelector('select[name="lang"]').value;
        window.changeLang(initialLang);
    })
    .catch(error => {
        console.error("CKEditor Init Error:", error);
    });
</script>

<?php include 'footer.php'; ?>
