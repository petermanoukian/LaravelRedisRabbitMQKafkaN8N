<div class="container-fluid px-0">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <div class="collapse navbar-collapse" id="adminNav" style="background-color: #343a40;">
                <!-- Left-aligned nav -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="catsDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Product categories
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="catsDropdown">
                            <li><a class="dropdown-item" href="{{ route('admin.cats.index') }}">List Categories</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.cats.create') }}">Add Category</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="filesDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Files
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="filesDropdown">
                            <li><a class="dropdown-item" href="#">Uploads</a></li>
                            <li><a class="dropdown-item" href="#">Reports</a></li>
                        </ul>
                    </li>
                </ul>

                <!-- Right-aligned admin dropdown -->
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-item" href="">Profile</a></li>
                            <li><a class="dropdown-item" href="">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item" type="submit">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>

