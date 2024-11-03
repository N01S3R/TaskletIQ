<?php App\Helpers\Template::partials('header_operator'); ?>

<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div class="container my-4">
        <nav aria-label="breadcrumb" class="main-breadcrumb mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Creator</a></li>
                <li class="breadcrumb-item"><a href="#">{{ pageTitle }} - {{ projectName }}</a></li>
            </ol>
        </nav>
        <header class="d-flex justify-content-end align-items-center m-4">
            <div>
                <a href="/creator/dashboard" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Pulpit
                </a>
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
<script src="https://cdn.jsdelivr.net/npm/vue@3"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.23.2/vuedraggable.umd.min.js"></script> -->
<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                pageTitle: <?= json_encode($data['pageTitle']) ?>,
                tasks: [],
                tasksData: <?= json_encode($data['tasks']) ?>,
                projectName: '', // Nowa zmienna na nazwę projektu
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
            if (Array.isArray(this.tasksData) && this.tasksData.length > 0) {
                this.projectName = this.tasksData[0].project_name;
            }
            if (Array.isArray(this.tasksData)) {
                this.tasks = this.tasksData.filter(task => task.task_progress === 0);
                this.inprogress = this.tasksData.filter(task => task.task_progress === 1);
                this.review = this.tasksData.filter(task => task.task_progress === 2);
                this.approved = this.tasksData.filter(task => task.task_progress === 3);
            } else {
                console.error('Dane z serwera są nieprawidłowe:', this.tasksData);
                this.loading = false;
            }
        },
        mounted() {
            // Inicjalizacja Dragula po załadowaniu komponentu
            dragula([
                    document.getElementById('tasks'),
                    document.getElementById('inprogress'),
                    document.getElementById('review'),
                    document.getElementById('approved')
                ])
                .on('drag', (el) => {
                    el.classList.add('is-moving');
                    this.togglePlaceholder(true);
                    el.dataset.previousProcess = el.getAttribute('data-process');
                })
                .on('dragend', (el) => {
                    el.classList.remove('is-moving');
                    this.togglePlaceholder(false);

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

            setTimeout(() => {
                this.loading = false;
            }, 3000);
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
                console.log(task);
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
                const foundTask = (
                    this.tasks.find(task => task.task_id == taskId) ||
                    this.inprogress.find(task => task.task_id == taskId) ||
                    this.review.find(task => task.task_id == taskId) ||
                    this.approved.find(task => task.task_id == taskId)
                );
                return foundTask || null;
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
        }
    }).mount('#app');
</script>



</body>

</html>