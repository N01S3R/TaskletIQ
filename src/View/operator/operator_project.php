<?php App\Helpers\Template::partials('header_operator'); ?>

<!-- <?php
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        ?> -->

<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div class="container">
        <header class="d-flex justify-content-between align-items-center m-4">
            <h3 class="p-2 mb-0">{{ pageTitle }}</h3>
            <div class="d-flex">
                <a href="/operator/dashboard" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Wróć</a>
            </div>
        </header>
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card bg-primary">
                    <div class="card-header">
                        <h4 class="card-title text-white">Zadania</h4>
                    </div>
                    <div class="card-body task-inner-list size" id="tasks" :data-process="0">
                        <div v-for="item in tasks" class="card mb-3" :data-task="item.task_id" :data-process="0">
                            <div class="card-body">
                                <h4 class="card-title">{{item.task_name}}</h4>
                                <p class="card-text">{{item.task_description}}</p>
                                <div class="text-center">
                                    <a :href="`http://todolist.t/operator/task/${item.task_id}`" class="btn btn-primary">Przejdź do zadania</a>
                                </div>
                            </div>
                        </div>
                        <div class="plus-container" v-show="isVisible" @click="addTask('tasks')">
                            +
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-danger">
                    <div class="card-header">
                        <h4 class="card-title text-white">Rozpoczęte</h4>
                    </div>
                    <div class="card-body task-inner-list size" id="inprogress" :data-process="1">
                        <div v-for="item in inprogress" class="card mb-3" :data-task="item.task_id" :data-process="1">
                            <div class="card-body">
                                <h4 class="card-title">{{item.task_name}}</h4>
                                <p class="card-text">{{item.task_description}}</p>
                                <div class="text-center">
                                    <a :href="`http://todolist.t/operator/task/${item.task_id}`" class="btn btn-primary">Przejdź do zadania</a>
                                </div>
                            </div>
                        </div>
                        <div class="plus-container" v-show="isVisible" @click="changeStatus">
                            +
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-warning">
                    <div class="card-header">
                        <h4 class="card-title text-white">W trakcie</h4>
                    </div>
                    <div class="card-body task-inner-list size" id="review" :data-process="2">
                        <div v-for="item in review" class="card mb-3" :data-task="item.task_id" :data-process="2">
                            <div class="card-body">
                                <h4 class="card-title">{{item.task_name}}</h4>
                                <p class="card-text">{{item.task_description}}</p>
                                <div class="text-center">
                                    <a :href="`http://todolist.t/operator/task/${item.task_id}`" class="btn btn-primary">Przejdź do zadania</a>
                                </div>
                            </div>
                        </div>
                        <div class="plus-container" v-show="isVisible" @click="changeStatus">
                            +
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-success">
                    <div class="card-header">
                        <h4 class="card-title text-white">Zakończone</h4>
                    </div>
                    <div class="card-body task-inner-list size" id="approved" :data-process="3">
                        <div v-for="item in approved" class="card mb-3" :data-task="item.task_id" :data-process="3">
                            <div class="card-body">
                                <h4 class="card-title">{{item.task_name}}</h4>
                                <p class="card-text">{{item.task_description}}</p>
                                <div class="text-center">
                                    <a :href="`http://todolist.t/operator/task/${item.task_id}`" class="btn btn-primary">Przejdź do zadania</a>
                                </div>
                            </div>
                        </div>
                        <div class="plus-container" v-show="isVisible" @click="changeStatus">
                            +
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.23.2/vuedraggable.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    var app = new Vue({
        el: '#app',
        data() {
            return {
                pageTitle: <?= json_encode($data['pageTitle']) ?>,
                tasks: [],
                inprogress: [],
                review: [],
                approved: [],
                placeholders: false,
                loading: true,
                isVisible: false,
                currentColumnId: ''
            };
        },
        created() {
            // Inicjalizacja danych z serwera
            const tasksData = <?= json_encode($data['tasks']) ?>;
            if (Array.isArray(tasksData)) {
                this.tasks = tasksData.filter(task => task.task_progress === '0');
                this.inprogress = tasksData.filter(task => task.task_progress === '1');
                this.review = tasksData.filter(task => task.task_progress === '2');
                this.approved = tasksData.filter(task => task.task_progress === '3');
            } else {
                console.error('Dane z serwera są nieprawidłowe:', tasksData);
            }

            // Inicjalizacja Dragula po załadowaniu dokumentu
            document.addEventListener('DOMContentLoaded', () => {
                dragula([
                        document.getElementById('tasks'),
                        document.getElementById('inprogress'),
                        document.getElementById('review'),
                        document.getElementById('approved')
                    ])
                    .on('drag', (el) => {
                        el.classList.add('is-moving');
                        this.togglePlaceholder(true); // `this` odnosi się do instancji Vue
                        el.dataset.previousProcess = el.getAttribute('data-process');
                    })
                    .on('dragend', (el) => {
                        el.classList.remove('is-moving');
                        this.togglePlaceholder(false); // `this` odnosi się do instancji Vue

                        const dest = el.parentNode;
                        if (dest) {
                            const newProcess = dest.getAttribute('data-process');
                            const previousProcess = el.dataset.previousProcess;
                            const taskData = el.getAttribute('data-task');

                            if (previousProcess !== newProcess) {
                                el.setAttribute('data-process', newProcess);
                                this.changeStatus(taskData, newProcess);
                            }
                        }
                    });
            });
        },
        methods: {
            togglePlaceholder(show) {
                this.isVisible = show;
            },
            addTask(columnId) {
                this.currentColumnId = columnId;
                // Możesz wywołać tutaj dodatkową funkcję lub logikę
                this.addTaskToColumn();
            },
            addTaskToColumn() {
                // Dodaj logikę do dodawania zadania do kolumny
                console.log(`Dodaj zadanie do kolumny: ${this.currentColumnId}`);
                // Na przykład, możesz otworzyć modal, aby użytkownik mógł dodać nowe zadanie
            },
            changeStatus(taskData, columnId) {
                const task = this.findTaskById(taskData);

                if (task) {
                    // Przygotuj dane do wysłania
                    const dataToSend = {
                        taskData: taskData,
                        columnId: columnId
                    };

                    // Wyślij dane asynchronicznie na backend
                    axios.post('/api/status', dataToSend)
                        .then(response => {
                            this.showNotification('success', response.data.message);
                            // Tutaj możesz wykonać odpowiednie akcje po udanym wysłaniu danych
                        })
                        .catch(error => {
                            this.showNotification('error', error);
                        });
                } else {
                    this.showNotification('error', 'Nie znaleziono zadania.');
                }
            },
            findTaskById(taskId) {
                return (this.tasks.find(task => task.task_id == taskId) ||
                    this.inprogress.find(task => task.task_id == taskId) ||
                    this.review.find(task => task.task_id == taskId) ||
                    this.approved.find(task => task.task_id == taskId)) || null;
            },
            showNotification(type, message) {
                const config = {
                    success: {
                        title: 'Sukces',
                        icon: 'bi bi-check-circle'
                    },
                    error: {
                        title: 'Błąd',
                        icon: 'bi bi-exclamation-triangle'
                    },
                    warning: {
                        title: 'Ostrzeżenie',
                        icon: 'bi bi-exclamation-circle'
                    },
                    info: {
                        title: 'Informacja',
                        icon: 'bi bi-info-circle'
                    },
                };

                const {
                    title = 'Powiadomienie', icon = 'bi bi-bell'
                } = config[type] || {};

                Lobibox.notify(type, {
                    msg: message,
                    icon: icon,
                    title: title,
                    sound: false,
                    position: 'top right',
                });
            },
        },
        mounted() {
            setTimeout(() => {
                this.loading = false;
            }, 3000);
        }
    });
</script>


</body>

</html>