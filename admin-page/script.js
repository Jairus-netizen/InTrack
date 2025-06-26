document.addEventListener('DOMContentLoaded', function () {
    // Logout confirmation handler
    document.querySelector('.logout-link').addEventListener('click', function(e) {
        e.preventDefault();
        const logoutUrl = this.href;
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out of the system",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create a hidden form and submit it to ensure proper logout
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = logoutUrl;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
    const toggleSidebar = document.querySelector('.toggle-sidebar');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');

    const sidebarState = localStorage.getItem('sidebarCollapsed');
    if (sidebarState === 'true') {
        sidebar.classList.add('collapsed');
        content.classList.add('expanded');
    } else if (sidebarState === 'false') {
        sidebar.classList.remove('collapsed');
        content.classList.remove('expanded');
    }

    toggleSidebar.addEventListener('click', function () {
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('expanded');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });

    // Active menu item highlighting
    function setActiveMenuItem() {
        const path = window.location.pathname;
        const currentPage = path.split('/').pop().split('.')[0];

        const aliases = {
            'all-notification': 'dashboard',
            'project-details': 'project-monitor',
            'edit_interns': 'interns',
            'admin_profile': 'dashboard'
        };
        const pageKey = aliases[currentPage] || currentPage;

        document.querySelectorAll('[data-page]').forEach(item => {
            item.classList.remove('active');
        });

        const match = document.querySelector(`[data-page="${pageKey}"]`);
        if (match) {
            match.classList.add('active');
        }
    }
    setActiveMenuItem();

    // Chart initialization
    const chartElement = document.getElementById('myChart');
    if (chartElement) {
        // Get the monthly data from the data attribute
        const monthlyData = JSON.parse(chartElement.dataset.monthlyData || '[]');

        chartElement.style.aspectRatio = '16 / 9';
        const cty = chartElement.getContext('2d');
        new Chart(cty, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Active Interns',
                    data: monthlyData.map(num => Math.round(num)), // Ensure whole numbers
                    backgroundColor: 'rgba(66, 133, 244, 0.8)',
                    borderColor: '#4285F4',
                    borderWidth: 1,
                    borderRadius: 4,
                    hoverBackgroundColor: 'rgba(66, 133, 244, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#333'
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        callbacks: {
                            label: function (context) {
                                return `${context.dataset.label}: ${Math.round(context.raw)}`; // Whole numbers in tooltip
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Active Interns',
                            color: '#666'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            color: '#666',
                            precision: 0 // Force whole numbers on y-axis
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month',
                            color: '#666'
                        },
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#666'
                        }
                    }
                }
            }
        });
    }

    // Responsive handling
    function handleResize() {
        if (window.innerWidth < 768) {
            sidebar.classList.add('collapsed');
            content.classList.add('expanded');
            localStorage.setItem('sidebarCollapsed', true);
        } else {
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                sidebar.classList.add('collapsed');
                content.classList.add('expanded');
            } else if (savedState === 'false') {
                sidebar.classList.remove('collapsed');
                content.classList.remove('expanded');
            }
        }
    }

    window.addEventListener('resize', handleResize);
    handleResize();
});