<div class="modal fade" id="edit<?= $row['id'] ?>" tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <form action="app/controllers/UserController.php" method="POST">

                <?= csrf_field(); ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?= htmlspecialchars($row['id']) ?>">

                <div class="modal-header bg-warning">

                    <h5 class="modal-title">

                        <i class="bi bi-pencil-square"></i>

                        Edit User

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label>

                                Nama Lengkap

                            </label>

                            <input
                                type="text"
                                name="fullname"
                                class="form-control"
                                value="<?= htmlspecialchars($row['fullName']) ?>"
                                required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>

                                Username

                            </label>

                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                value="<?= htmlspecialchars($row['username']) ?>"
                                required>

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label>

                                Email

                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?= htmlspecialchars($row['email']) ?>"
                                required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>

                                Password Baru

                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Kosongkan jika tidak diubah">

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label>

                                Role

                            </label>

                            <select
                                name="role"
                                class="form-select">

                                <option
                                value="Administrator"
                                <?= $row['role']=="Administrator" ? "selected" : "" ?>>

                                Administrator

                                </option>

                                <option
                                value="User"
                                <?= $row['role']=="User" ? "selected" : "" ?>>

                                User

                                </option>

                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>

                                Status

                            </label>

                            <select
                                name="status"
                                class="form-select">

                                <option
                                value="active"
                                <?= $row['status']=="active" ? "selected" : "" ?>>

                                Active

                                </option>

                                <option
                                value="inactive"
                                <?= $row['status']=="inactive" ? "selected" : "" ?>>

                                Inactive

                                </option>

                            </select>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button
                        type="submit"
                        name="update"
                        class="btn btn-warning">

                        <i class="bi bi-save"></i>

                        Update

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
