<?php
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$site_title = 'Video Consultation';

$room = isset($_GET['room']) ? preg_replace('/[^a-zA-Z0-9\-]/', '', $_GET['room']) : '';
$db = getDB();
$consultation = null;

if (empty($room) && isset($_GET['appointment_id'])) {
    $aptId = (int)$_GET['appointment_id'];
    $stmt = $db->prepare("SELECT c.*, d.name as doctor_name, d.image_url as doctor_image
        FROM consultations c JOIN doctors d ON c.doctor_id = d.id
        WHERE c.appointment_id = ? AND c.user_id = ?");
    $stmt->bind_param("ii", $aptId, $_SESSION['user_id']);
    $stmt->execute();
    $consultation = $stmt->get_result()->fetch_assoc();
    if ($consultation && $consultation['meeting_link']) {
        $room = basename($consultation['meeting_link']);
    }
}

if (empty($room)) {
    $room = 'pca-' . bin2hex(random_bytes(4));
}

$domain = 'meet.jit.si';

include __DIR__ . '/includes/header.php';
?>
<div class="min-h-screen bg-surface">
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-headline-md font-headline-md text-primary">Video Consultation</h1>
                <p class="text-on-surface-variant text-sm">Room: <?= htmlspecialchars($room) ?></p>
            </div>
            <div class="flex gap-3">
                <button onclick="copyLink()" class="px-4 py-2 bg-white border border-outline-variant rounded-lg text-sm hover:bg-surface-container transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">link</span> Copy Link
                </button>
                <a href="<?= BASE_URL ?>/my-health.php" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">call_end</span> End Call
                </a>
            </div>
        </div>
        <div id="jitsi-container" class="w-full rounded-xl overflow-hidden shadow-lg" style="height: 70vh;"></div>
    </div>
</div>

<script src="https://meet.jit.si/external_api.js"></script>
<script>
const domain = '<?= $domain ?>';
const options = {
    roomName: '<?= $room ?>',
    parentNode: document.querySelector('#jitsi-container'),
    userInfo: {
        displayName: '<?= htmlspecialchars($_SESSION['user_name'] ?? 'Patient') ?>'
    },
    configOverrides: {
        startWithAudioMuted: true,
        startWithVideoMuted: false,
        disableDeepLinking: true,
        prejoinPageEnabled: false
    },
    interfaceConfigOverrides: {
        SHOW_JITSI_WATERMARK: false,
        SHOW_WATERMARK_FOR_GUESTS: false,
        TOOLBAR_ALWAYS_VISIBLE: true,
        DISABLE_JOIN_LEAVE_NOTIFICATIONS: true
    }
};
const jitsiApi = new JitsiMeetExternalAPI(domain, options);

jitsiApi.addListener('videoConferenceLeft', () => {
    window.location.href = '<?= BASE_URL ?>/my-health.php';
});

jitsiApi.addListener('participantRoleChanged', function(event) {
    if (event.role === 'moderator') {
    }
});

function copyLink() {
    const link = window.location.origin + '<?= BASE_URL ?>/video-consult.php?room=<?= $room ?>';
    navigator.clipboard.writeText(link).then(() => {
        alert('Meeting link copied!');
    });
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
