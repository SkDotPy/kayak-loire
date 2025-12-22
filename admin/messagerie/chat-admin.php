<?php
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

requireAdmin();

$page_title = 'Messagerie Admin';

// Récupérer le client sélectionné (si conversation ouverte)
$client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;

// Récupérer la liste de tous les clients ayant des messages
$conversations = query("
    SELECT 
        u.id as user_id,
        u.prenom,
        u.nom,
        u.email,
        (SELECT COUNT(*) FROM messages WHERE user_id = u.id AND is_admin = 0 AND lu = 0) as nb_non_lus,
        (SELECT m.message FROM messages m WHERE m.user_id = u.id ORDER BY m.created_at DESC LIMIT 1) as dernier_message,
        (SELECT m.created_at FROM messages m WHERE m.user_id = u.id ORDER BY m.created_at DESC LIMIT 1) as derniere_date
    FROM users u
    WHERE u.role = 'client'
    AND EXISTS (SELECT 1 FROM messages WHERE user_id = u.id)
    ORDER BY derniere_date DESC
");

// Si un client est sélectionné, récupérer ses messages
$messages = [];
$client_info = null;

if ($client_id > 0) {
    // Récupérer les infos du client
    $client_info_result = query("SELECT id, prenom, nom, email FROM users WHERE id = ? AND role = 'client'", [$client_id]);
    
    if (!empty($client_info_result)) {
        $client_info = $client_info_result[0];
        
        // Récupérer tous les messages de cette conversation
        $messages = query("
            SELECT * FROM messages 
            WHERE user_id = ? 
            ORDER BY created_at ASC
        ", [$client_id]);
        
        // Marquer tous les messages du client comme "lus"
        execute("UPDATE messages SET lu = 1 WHERE user_id = ? AND is_admin = 0", [$client_id]);
    }
}

// Traitement de l'envoi d'un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && $client_id > 0) {
    $message = clean($_POST['message']);
    
    if (!empty($message)) {
        $sql = "INSERT INTO messages (user_id, message, is_admin, lu, created_at) 
                VALUES (?, ?, 1, 1, NOW())";
        
        $result = execute($sql, [$client_id, $message]);
        
        if ($result) {
            // Rediriger pour rafraîchir et éviter la double soumission
            redirect(SITE_URL . '/admin/messagerie/chat-admin.php?client_id=' . $client_id);
        }
    }
}

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>💬 Messagerie Admin</h1>
        </div>

        <div class="messagerie-container">
            
            <!-- SIDEBAR : Liste des conversations -->
            <div class="conversations-sidebar">
                <div class="sidebar-header">
                    <h3>Conversations</h3>
                    <span class="badge badge-info"><?php echo count($conversations); ?></span>
                </div>

                <div class="conversations-list">
                    <?php if (empty($conversations)): ?>
                        <div class="empty-conversations">
                            <p>Aucune conversation pour le moment</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($conversations as $conv): ?>
                            <a href="<?php echo SITE_URL; ?>/admin/messagerie/chat-admin.php?client_id=<?php echo $conv['user_id']; ?>" 
                               class="conversation-item <?php echo ($client_id == $conv['user_id']) ? 'active' : ''; ?>">
                                
                                <div class="conversation-avatar">
                                    <?php echo strtoupper(substr($conv['prenom'], 0, 1) . substr($conv['nom'], 0, 1)); ?>
                                </div>
                                
                                <div class="conversation-info">
                                    <div class="conversation-header">
                                        <span class="conversation-name"><?php echo htmlspecialchars($conv['prenom'] . ' ' . $conv['nom']); ?></span>
                                        <?php if ($conv['nb_non_lus'] > 0): ?>
                                            <span class="badge-notif"><?php echo $conv['nb_non_lus']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="conversation-preview">
                                        <?php echo htmlspecialchars(substr($conv['dernier_message'], 0, 50)) . '...'; ?>
                                    </div>
                                    <div class="conversation-date">
                                        <?php 
                                        $date = new DateTime($conv['derniere_date']);
                                        $now = new DateTime();
                                        $diff = $now->diff($date);
                                        
                                        if ($diff->days == 0) {
                                            echo $date->format('H:i');
                                        } elseif ($diff->days == 1) {
                                            echo 'Hier';
                                        } else {
                                            echo $date->format('d/m/Y');
                                        }
                                        ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ZONE DE CHAT -->
            <div class="chat-zone">
                <?php if ($client_id == 0): ?>
                    <!-- Aucune conversation sélectionnée -->
                    <div class="no-conversation-selected">
                        <div class="no-conv-icon">💬</div>
                        <h3>Sélectionnez une conversation</h3>
                        <p>Choisissez un client dans la liste pour voir les messages</p>
                    </div>
                <?php elseif (!$client_info): ?>
                    <!-- Client invalide -->
                    <div class="no-conversation-selected">
                        <div class="no-conv-icon">⚠️</div>
                        <h3>Client introuvable</h3>
                        <p>Ce client n'existe pas ou n'a pas de messages</p>
                    </div>
                <?php else: ?>
                    <!-- Conversation active -->
                    <div class="chat-header">
                        <div class="chat-user-info">
                            <div class="chat-avatar">
                                <?php echo strtoupper(substr($client_info['prenom'], 0, 1) . substr($client_info['nom'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="chat-user-name"><?php echo htmlspecialchars($client_info['prenom'] . ' ' . $client_info['nom']); ?></div>
                                <div class="chat-user-email"><?php echo htmlspecialchars($client_info['email']); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="chat-messages" id="chatMessages">
                        <?php if (empty($messages)): ?>
                            <div class="no-messages">Aucun message dans cette conversation</div>
                        <?php else: ?>
                            <?php foreach($messages as $msg): ?>
                                <div class="message <?php echo $msg['is_admin'] ? 'message-admin' : 'message-client'; ?>">
                                    <div class="message-bubble">
                                        <div class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                                        <div class="message-time">
                                            <?php 
                                            $date = new DateTime($msg['created_at']);
                                            echo $date->format('H:i');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="chat-input-zone">
                        <form method="POST" class="chat-form" id="chatForm">
                            <textarea 
                                name="message" 
                                id="messageInput" 
                                placeholder="Écrivez votre message..." 
                                rows="2" 
                                required></textarea>
                            <button type="submit" class="btn btn-primary">
                                <span>Envoyer</span>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-outline">Retour au dashboard</a>
        </div>
    </div>
</div>

<style>
.messagerie-container {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 0;
    height: 700px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* ============================================
   SIDEBAR CONVERSATIONS
   ============================================ */
.conversations-sidebar {
    border-right: 2px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    background: #f9fafb;
}

.sidebar-header {
    padding: 20px;
    background: white;
    border-bottom: 2px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar-header h3 {
    margin: 0;
    font-size: 18px;
    color: var(--dark);
}

.conversations-list {
    flex: 1;
    overflow-y: auto;
}

.empty-conversations {
    padding: 40px 20px;
    text-align: center;
    color: #6b7280;
}

.conversation-item {
    display: flex;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    transition: background 0.2s;
}

.conversation-item:hover {
    background: white;
}

.conversation-item.active {
    background: var(--primary-color);
    color: white;
}

.conversation-item.active .conversation-preview,
.conversation-item.active .conversation-date {
    color: rgba(255,255,255,0.8);
}

.conversation-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    flex-shrink: 0;
}

.conversation-item.active .conversation-avatar {
    background: white;
    color: var(--primary-color);
}

.conversation-info {
    flex: 1;
    min-width: 0;
}

.conversation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.conversation-name {
    font-weight: 600;
    font-size: 15px;
}

.badge-notif {
    background: #ef4444;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.conversation-item.active .badge-notif {
    background: white;
    color: #ef4444;
}

.conversation-preview {
    font-size: 13px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 4px;
}

.conversation-date {
    font-size: 12px;
    color: #9ca3af;
}

/* ============================================
   ZONE DE CHAT
   ============================================ */
.chat-zone {
    display: flex;
    flex-direction: column;
    background: white;
}

.no-conversation-selected {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #6b7280;
}

.no-conv-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.no-conversation-selected h3 {
    margin: 0 0 10px 0;
    color: var(--dark);
}

.chat-header {
    padding: 20px;
    border-bottom: 2px solid #e5e7eb;
    background: white;
}

.chat-user-info {
    display: flex;
    gap: 12px;
    align-items: center;
}

.chat-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
}

.chat-user-name {
    font-weight: 600;
    font-size: 16px;
    color: var(--dark);
}

.chat-user-email {
    font-size: 13px;
    color: #6b7280;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f9fafb;
}

.no-messages {
    text-align: center;
    color: #9ca3af;
    padding: 40px;
}

.message {
    margin-bottom: 16px;
    display: flex;
}

.message-client {
    justify-content: flex-start;
}

.message-admin {
    justify-content: flex-end;
}

.message-bubble {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 16px;
}

.message-client .message-bubble {
    background: white;
    border-bottom-left-radius: 4px;
}

.message-admin .message-bubble {
    background: var(--primary-color);
    color: white;
    border-bottom-right-radius: 4px;
}

.message-text {
    font-size: 15px;
    line-height: 1.5;
    margin-bottom: 4px;
}

.message-time {
    font-size: 11px;
    opacity: 0.7;
}

.chat-input-zone {
    padding: 20px;
    border-top: 2px solid #e5e7eb;
    background: white;
}

.chat-form {
    display: flex;
    gap: 12px;
    align-items: flex-end;
}

.chat-form textarea {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 24px;
    font-size: 15px;
    font-family: inherit;
    resize: none;
    outline: none;
    transition: border-color 0.2s;
}

.chat-form textarea:focus {
    border-color: var(--primary-color);
}

.chat-form button {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 24px;
}

/* Scrollbar personnalisée */
.conversations-list::-webkit-scrollbar,
.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.conversations-list::-webkit-scrollbar-track,
.chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.conversations-list::-webkit-scrollbar-thumb,
.chat-messages::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

/* Responsive */
@media (max-width: 768px) {
    .messagerie-container {
        grid-template-columns: 1fr;
        height: auto;
    }
    
    .conversations-sidebar {
        height: 300px;
    }
    
    .chat-zone {
        height: 500px;
    }
}
</style>

<script>
// Auto-scroll vers le bas des messages
const chatMessages = document.getElementById('chatMessages');
if (chatMessages) {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Rafraîchir automatiquement toutes les 5 secondes si une conversation est ouverte
<?php if ($client_id > 0): ?>
setInterval(() => {
    location.reload();
}, 5000);
<?php endif; ?>

// Focus automatique sur le champ de saisie
const messageInput = document.getElementById('messageInput');
if (messageInput) {
    messageInput.focus();
}

// Submit avec Ctrl+Enter
messageInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && e.ctrlKey) {
        document.getElementById('chatForm').submit();
    }
});
</script>

<?php include '../../includes/footer.php'; ?>