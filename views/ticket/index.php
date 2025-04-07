<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap5\Modal;
use app\models\TicketMessage;
use app\models\User;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'My Tickets';
$this->params['breadcrumbs'][] = $this->title;

// Register required assets
$this->registerJsFile('https://cdn.jsdelivr.net/npm/sweetalert2@11', [
    'position' => \yii\web\View::POS_HEAD,
    'depends' => [\yii\web\JqueryAsset::class]
]);
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

// Register Bootstrap 5 JS
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', [
    'position' => \yii\web\View::POS_HEAD,
    'integrity' => 'sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p',
    'crossorigin' => 'anonymous',
]);
?>

<!-- Responsive meta tags -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="csrf-token" content="<?= Yii::$app->request->getCsrfToken() ?>">

<!-- Responsive CSS -->
<style>
/* Responsive Ticket Page CSS */
:root {
  --primary-color: #1B1D4E;
  --accent-color: #EA5626;
  --accent-hover: rgb(230, 94, 48);
  --background-color: #f8f9fa;
  --border-color: #dee2e6;
  --table-hover: #e3f2fd;
  --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
  --text-dark: #333;
  --text-muted: #6c757d;
  --success: #10b981;
  --warning: #ffc107;
  --danger: #dc3545;
  --info: #17a2b8;
}

/* Global Container Styles */
.container {
  max-width: 100%;
  padding: 0;
}

body {
  overflow-x: hidden;
}

/* Ticket Container */
.ticket-index {
  width: 100%;
  margin: 0 auto;
  border-radius: 8px;
  transition: padding 0.3s ease;
}

/* Page Header */
.ticket-index h1 {
  margin-bottom: 1.5rem;
  color: var(--primary-color);
}

/* Create Ticket Button */
.custom-btn {
  background-color: var(--accent-color);
  color: white;
  border: none;
  font-weight: 500;
  transition: background-color 0.3s ease, transform 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.25rem;
}

.custom-btn:hover {
  background-color: var(--accent-hover);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.custom-btn i {
  font-size: 1.1rem;
}

/* Table Styles */
.custom-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.custom-table thead th {
  color: var(--primary-color) !important;
  font-weight: 600;
  background-color: var(--background-color);
  border-bottom: 2px solid var(--border-color);
  padding: 0.75rem 1rem;
  white-space: nowrap;
}

.custom-table tbody tr {
  transition: background-color 0.2s ease;
}

.custom-table tbody tr:hover {
  background-color: var(--table-hover);
}

.custom-table td {
  padding: 0.75rem 1rem;
  vertical-align: middle;
  border-top: none;
  border-bottom: 1px solid var(--border-color);
}

/* Status Badges */
.badge {
  padding: 0.4em 0.8em;
  font-weight: 500;
  border-radius: 50px;
  display: inline-block;
}

.bg-warning {
  background-color: var(--warning) !important;
  color: #212529 !important;
}

.bg-success {
  background-color: var(--success) !important;
}

.bg-danger {
  background-color: var(--danger) !important;
}

.bg-primary {
  background-color: var(--primary-color) !important;
}

.bg-info {
  background-color: var(--info) !important;
}

/* Action Buttons */
.btn-xs {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  line-height: 1.5;
  border-radius: 0.2rem;
}

.btn-danger {
  background-color: var(--danger);
  border-color: var(--danger);
  color: white;
}

.btn-danger:hover {
  background-color: #c82333;
  border-color: #bd2130;
}

.btn-warning {
  background-color: var(--warning);
  border-color: var(--warning);
  color: #212529;
}

.btn-warning:hover {
  background-color: #e0a800;
  border-color: #d39e00;
}

.btn-info {
  background-color: var(--info);
  border-color: var(--info);
  color: white;
}

.btn-info:hover {
  background-color: #138496;
  border-color: #117a8b;
}

/* Messages Modal Styles */
.messages-container {
  max-height: 70vh;
  overflow-y: auto;
  padding: 1rem;
}

.message-item {
  border-left: 4px solid var(--info);
  margin-bottom: 1rem;
  padding: 1rem;
  background-color: var(--background-color);
  border-radius: 0.25rem;
  box-shadow: var(--shadow-sm);
  transition: transform 0.2s ease;
}

.message-item:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.message-header {
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 0.5rem;
  margin-bottom: 0.75rem;
  font-weight: 600;
}

.message-content {
  white-space: pre-wrap;
  word-break: break-word;
  line-height: 1.5;
}

.message-meta {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-top: 0.75rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.message-meta i {
  margin-right: 0.25rem;
}

.message-closure {
  border-left-color: var(--danger);
}

.message-system {
  border-left-color: var(--success);
}

/* Modal Styles */
.modal-header {
  background-color: var(--primary-color);
  color: white;
  border-radius: 0.3rem 0.3rem 0 0;
}

.modal-header .close {
  color: white;
  opacity: 0.8;
}

.modal-header .close:hover {
  opacity: 1;
}

.modal-title i {
  margin-right: 0.5rem;
}

/* Screenshot Modal */
#screenshotImage {
  max-width: 100%;
  height: auto;
  display: block;
  margin: 0 auto;
}

#modalContent {
  text-align: center;
}

/* Responsive Styles */
@media (min-width: 1400px) {
  .ticket-index {
    padding: 80px 120px !important;
    margin-top: -40px;
  }
  
  .custom-table td,
  .custom-table th {
    padding: 1rem 1.25rem;
  }
}

@media (min-width: 992px) and (max-width: 1399px) {
  .ticket-index {
    padding: 80px 60px !important;
    margin-top: -40px;
  }
}

@media (min-width: 768px) and (max-width: 991px) {
  .ticket-index {
    padding: 60px 40px !important;
    margin-top: -40px;
  }
  
  .custom-table {
    font-size: 0.95rem;
  }
}

@media (max-width: 767px) {
  /* Mobile styling */
  .ticket-index {
    padding: 40px 20px !important;
    margin-top: -20px;
  }
  
  .ticket-index h1 {
    font-size: 1.75rem;
    text-align: center;
  }
  
  .grid-view .table-responsive {
    border: none;
    margin-bottom: 0;
  }
  
  /* Force table to not be like tables on mobile */
  .grid-view table, 
  .grid-view thead, 
  .grid-view tbody, 
  .grid-view th, 
  .grid-view td, 
  .grid-view tr {
    display: block;
  }
  
  .grid-view thead tr {
    position: absolute;
    top: -9999px;
    left: -9999px;
  }
  
  .grid-view tr {
    border: 1px solid var(--border-color);
    margin-bottom: 1rem;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
  }
  
  .grid-view td {
    /* Behave like a "row" */
    border: none;
    border-bottom: 1px solid var(--border-color);
    position: relative;
    padding-left: 50% !important;
    white-space: normal;
    text-align: left;
    min-height: 30px;
  }
  
  .grid-view td:last-child {
    border-bottom: none;
  }
  
  /* Add a label to each cell */
  .grid-view td:before {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    width: 45%;
    padding-right: 10px;
    white-space: nowrap;
    text-align: left;
    font-weight: bold;
    content: attr(data-col-name);
  }
  
  /* Hide the first column (serial number) */
  .grid-view td:first-child {
    display: none;
  }
  
  /* Custom button styling for mobile */
  .custom-btn {
    max-width: 100% !important;
    margin: 0 auto;
  }
}

@media (max-width: 576px) {
  .ticket-index {
    padding: 30px 15px !important;
  }
  
  .ticket-index h1 {
    font-size: 1.5rem;
  }
  
  .modal-dialog {
    margin: 0.5rem;
  }
  
  .messages-container {
    max-height: 60vh;
  }
  
  .message-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }
}

/* Animation for loading indicators */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.fa-spinner {
  animation: spin 1s linear infinite;
}
</style>

<div class="container ticket-index">
    <h1 class="fw-semibold"><?= Html::encode($this->title) ?></h1>

    <p class="text-center">
        <?= Html::a(
            '<i class="fas fa-plus-circle"></i> Create Ticket',
            ['create'],
            [
                'class' => 'btn custom-btn w-100 p-2 mt-3 rounded-1 d-flex align-items-center justify-content-center gap-2',
                'style' => 'max-width: 220px;'
            ]
        ) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-striped table-hover shadow-sm custom-table', 'id' => 'custom-table'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            [
                'attribute' => 'company_name',
                'value' => fn($model) => $model->company_name ?: Yii::$app->user->identity->company_name,
            ],
            [
                'attribute' => 'module',
                'value' => fn($model) => $model['module'] ?: '(not set)',
            ],
            [
                'attribute' => 'issue',
                'value' => fn($model) => $model['issue'] ?: '(not set)',
            ],
            [
                'attribute' => 'description',
                'format' => 'ntext',
                'contentOptions' => ['style' => 'max-width:350px; overflow:hidden; text-overflow:ellipsis;'],
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    $statusClasses = [
                        'pending' => 'badge bg-warning text-dark',
                        'closed' => 'badge bg-success',
                        'escalated' => 'badge bg-danger',
                    ];
                    $class = $statusClasses[$model->status] ?? 'badge bg-primary';
                    return Html::tag('span', ucfirst($model->status), ['class' => $class]);
                }
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'screenshot',
                'format' => 'raw',
                'value' => function ($model) {
                    if (!empty($model->screenshot_url) || !empty($model->screenshot)) {
                        return Html::a('<i class="fa fa-eye"></i> View Screenshot', '#', [
                            'class' => 'view-screenshot',
                            'data-screenshot' => !empty($model->screenshot_url) ? $model->screenshot_url : $model->screenshot,
                            'data-toggle' => 'modal',
                            'data-target' => '#screenshotModal'
                        ]);
                    }
                    return '<span class="text-muted">No screenshot</span>';
                }
            ],
            [
                'attribute' => 'latest_message',
                'label' => 'Latest Message',
                'format' => 'raw',
                'value' => function ($model) {
                    $latestMessage = TicketMessage::find()
                        ->where(['ticket_id' => $model->id])
                        ->andWhere(['is_internal' => 0])
                        ->orderBy(['sent_at' => SORT_DESC])
                        ->one();

                    if ($latestMessage) {
                        $sender = User::findOne($latestMessage->sender_id);
                        $senderName = $sender ? Html::encode($sender->name) : 'Unknown';
                        $messagePreview = Html::encode(Yii::$app->formatter->asDate($latestMessage->sent_at)) .
                            ' by ' . $senderName;

                        return Html::button('View Message', [
                            'class' => 'btn btn-sm btn-info view-messages',
                            'data-ticket-id' => $model->id,
                            'title' => $messagePreview
                        ]);
                    }
                    return '<span class="text-muted">(No messages)</span>';
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete} {close}',
                'buttons' => [
                    'delete' => fn($url, $model) => Html::a('Delete', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-xs',
                        'data' => ['confirm' => 'Are you sure you want to delete this ticket?', 'method' => 'post'],
                    ]),
                    'close' => function ($url, $model) {
                        if ($model->status !== 'closed') {
                            return Html::button('<i class="fas fa-times-circle"></i> Close', [
                                'class' => 'btn btn-warning btn-xs close-ticket',
                                'data-id' => $model->id,
                                'type' => 'button',
                                'title' => 'Close this ticket'
                            ]);
                        }
                        return '<span class="badge bg-success">Closed</span>';
                    },
                ],
                'contentOptions' => ['style' => 'min-width:180px;'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>

<!-- Screenshot Modal -->
<?php
Modal::begin([
    'id' => 'screenshotModal',
    'size' => Modal::SIZE_LARGE,
    'title' => '<h4 class="modal-title"><i class="fas fa-image"></i> Screenshot</h4>',
    'options' => ['class' => 'modal fade'],
]);

echo '<div id="modalContent"><img id="screenshotImage" class="img-responsive" style="width: 100%;" /></div>';

Modal::end();
?>

<!-- Messages Modal -->
<div class="modal fade" id="messagesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-comments"></i> 
                    Ticket Messages
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="messagesList" class="messages-container">
                    <!-- Messages will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Responsive JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded: Initializing ticket page JS');
    
    // Add data attributes for mobile view column names
    enhanceTableForMobile();
    
    // Initialize screenshot modal functionality
    initScreenshotModal();
    
    // Initialize ticket closing functionality
    initTicketClose();
    
    // Initialize messages modal functionality
    initMessagesModal();
});

/**
 * Add data-col-name attributes to table cells for mobile responsive view
 */
function enhanceTableForMobile() {
    const table = document.getElementById('custom-table');
    if (!table) return;
    
    // Get header text from table headers
    const headerCells = table.querySelectorAll('thead th');
    const headerTexts = Array.from(headerCells).map(th => th.textContent.trim());
    
    // Add data attributes to each cell in the table body
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        cells.forEach((cell, index) => {
            if (headerTexts[index]) {
                cell.setAttribute('data-col-name', headerTexts[index]);
            }
        });
    });
}

/**
 * Initialize screenshot modal functionality
 */
function initScreenshotModal() {
    // View screenshot click handlers
    const screenshotLinks = document.querySelectorAll('.view-screenshot');
    
    screenshotLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const screenshotSrc = this.getAttribute('data-screenshot');
            const modal = document.getElementById('screenshotModal');
            const modalImage = document.getElementById('screenshotImage');
            
            if (modalImage && screenshotSrc) {
                modalImage.src = screenshotSrc;
                
                // If using Bootstrap 5
                if (typeof bootstrap !== 'undefined') {
                    const screenshotModal = new bootstrap.Modal(modal);
                    screenshotModal.show();
                } 
                // If using jQuery with Bootstrap 4
                else if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
                    $(modal).modal('show');
                }
            }
        });
    });
}

/**
 * Initialize ticket closing functionality with improved UX
 */
function initTicketClose() {
    const closeButtons = document.querySelectorAll('.close-ticket');
    
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const ticketId = this.getAttribute('data-id');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            // Confirm before proceeding
            if (typeof Swal !== 'undefined') {
                // Use SweetAlert2 if available
                Swal.fire({
                    title: 'Close Ticket',
                    text: 'Are you sure you want to close this ticket?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, close it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        closeTicket(btn, ticketId, csrfToken);
                    }
                });
            } else {
                // Fallback to standard confirm
                if (confirm('Are you sure you want to close this ticket?')) {
                    closeTicket(btn, ticketId, csrfToken);
                }
            }
        });
    });
}

/**
 * Handle the AJAX request to close a ticket
 */
function closeTicket(btn, ticketId, csrfToken) {
    // Show loading state
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Closing...';
    btn.disabled = true;
    
    // Create form data
    const formData = new FormData();
    formData.append('id', ticketId);
    formData.append('_csrf', csrfToken);
    
    // Send AJAX request
    fetch('/ticket/close', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('Success', data.message || 'Ticket closed successfully', 'success');
            
            // Update UI
            const row = btn.closest('tr');
            const statusCell = row.querySelector('td:nth-child(7)'); // Status column
            
            if (statusCell) {
                statusCell.innerHTML = '<span class="badge bg-success">Closed</span>';
            }
            
            // Replace button with closed status
            btn.outerHTML = '<span class="badge bg-success">Closed</span>';
        } else {
            // Show error message
            showNotification('Error', data.message || 'Failed to close ticket', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error closing ticket:', error);
        showNotification('Error', 'An error occurred while closing the ticket', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

/**
 * Initialize messages modal functionality with improved error handling
 */
function initMessagesModal() {
    // Add click event to all view message buttons
    const messageButtons = document.querySelectorAll('.view-messages');
    
    messageButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const ticketId = this.getAttribute('data-ticket-id');
            const messagesContainer = document.getElementById('messagesList');
            const messagesModal = document.getElementById('messagesModal');
            
            console.log('Loading messages for ticket ID:', ticketId);
            
            if (messagesContainer) {
                // Show loading state
                messagesContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading messages...</div>';
                
                // Show modal
                if (typeof bootstrap !== 'undefined') {
                    const modalElement = document.getElementById('messagesModal');
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
                    $(messagesModal).modal('show');
                }
                
                // Fetch messages with more detailed error handling
                fetch('<?= \yii\helpers\Url::to(['/ticket/get-messages']) ?>?ticket_id=' + ticketId, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Received data:', data);
                        
                        if (data.success) {
                            let messagesHtml = '';
                            
                            if (data.messages.length === 0) {
                                messagesHtml = '<div class="text-center text-muted">No messages found</div>';
                            } else {
                                data.messages.forEach(function(message) {
                                    let messageClass = 'message-item';
                                    if (message.message_type === 'closure_message') {
                                        messageClass += ' message-closure';
                                    } else if (message.message_type === 'system') {
                                        messageClass += ' message-system';
                                    }
                                    
                                    messagesHtml += `
                                        <div class="${messageClass}">
                                            <div class="message-header">
                                                <strong>${message.subject || 'No Subject'}</strong>
                                            </div>
                                            <div class="message-content">
                                                ${message.message}
                                            </div>
                                            <div class="message-meta">
                                                <span><i class="far fa-clock"></i> ${message.sent_at}</span>
                                                <span><i class="fas fa-user"></i> ${message.sender_name}</span>
                                            </div>
                                        </div>
                                    `;
                                });
                            }
                            
                            messagesContainer.innerHTML = messagesHtml;
                        } else {
                            console.error('API returned error:', data.message);
                            messagesContainer.innerHTML = `
                                <div class="alert alert-danger">
                                    Error loading messages: ${data.message || 'Unknown error'}
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading messages:', error);
                        messagesContainer.innerHTML = `
                            <div class="alert alert-danger">
                                Failed to load messages. Please try again.<br>
                                <small>${error.message}</small>
                            </div>
                        `;
                    });
            }
        });
    });
}

/**
 * Helper function to show notifications
 */
function showNotification(title, message, type) {
    if (typeof Swal !== 'undefined') {
        // Use SweetAlert2 if available
        Swal.fire({
            title: title,
            text: message,
            icon: type,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        // Fallback to alert
        alert(`${title}: ${message}`);
    }
}
</script>