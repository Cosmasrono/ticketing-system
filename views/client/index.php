<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $clients app\models\Client[] */

$this->title = 'Client Management';
?>

<style>
.client-dashboard {
    padding: 20px;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.client-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}

.card-header {
    background: linear-gradient(45deg, #4e73df, #36b9cc);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table {
    margin: 0;
}

.table thead th {
    background-color: #f8f9fc;
    color: #4e73df;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    padding: 1rem;
    border-bottom: 2px solid #e3e6f0;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fc;
}

.table td {
    padding: 1rem;
    vertical-align: middle;
}

.badge-module {
    background: #e3e6f0;
    color: #4e73df;
    padding: 0.4em 0.8em;
    border-radius: 50px;
    font-size: 0.75rem;
    margin: 0.2rem;
    display: inline-block;
}

.status-active {
    color: #1cc88a;
}

.status-inactive {
    color: #e74a3b;
}

.search-box {
    max-width: 300px;
    margin-bottom: 1rem;
}

.action-buttons a {
    margin-left: 0.5rem;
}
</style>

<div class="client-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><?= Html::encode($this->title) ?></h1>
            <p class="mb-0 text-gray-600">Total Clients: <?= count($clients) ?></p>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-plus"></i> Add New Client', ['site/add-client'], [
                'class' => 'btn btn-primary'
            ]) ?>
        </div>
    </div>

    <!-- Client List Card -->
    <div class="card client-card">
        <div class="card-header">
            <h3 class="mb-0">Client List</h3>
            <div class="search-box">
                <input type="text" id="clientSearch" class="form-control" placeholder="Search clients...">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="clientTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Company Name</th>
                            <th>Email</th>
                            <th>Modules</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><strong>#<?= Html::encode($client->id) ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            <?= strtoupper(substr($client->company_name, 0, 1)) ?>
                                        </div>
                                        <?= Html::encode($client->company_name) ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:<?= Html::encode($client->company_email) ?>">
                                        <?= Html::encode($client->company_email) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    $modules = !empty($client->module) ? explode(',', $client->module) : [];
                                    foreach ($modules as $module):
                                        if (trim($module)):
                                    ?>
                                        <span class="badge-module"><?= Html::encode(trim($module)) ?></span>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </td>
                                <td>
                                    <span class="status-active">
                                        <i class="fas fa-circle fa-sm"></i> Active
                                    </span>
                                </td>
                                <td>
                                    <div><?= Yii::$app->formatter->asDate($client->created_at) ?></div>
                                    <small class="text-muted">
                                        <?= Yii::$app->formatter->asTime($client->created_at) ?>
                                    </small>
                                </td>
                                <td class="action-buttons">
                                    <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $client->id], [
                                        'class' => 'btn btn-sm btn-outline-primary',
                                        'title' => 'Edit Client'
                                    ]) ?>
                                    <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $client->id], [
                                        'class' => 'btn btn-sm btn-outline-info',
                                        'title' => 'View Details'
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Register required assets
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

// Add search functionality
$this->registerJs("
    $('#clientSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#clientTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
");
?> 