<template>
  <div class="col-sm-12">
    <section class="content-header" style="margin-bottom: 25px;">
      <div class="row">
        <h1 class="col-md-11">Create New User</h1>
        <button class="col-md-1 col-sm-2 btn btn-primary" @click="goBack()">Back</button>
      </div>
    </section>

    <div class="card">
      <div class="card-body">
        <form class="needs-validation" novalidate>
          <div class="form-group row">
            <label for="username" class="col-sm-2 col-form-label">Username</label>
            <div class="col-sm-10">
              <input
                type="text"
                class="form-control"
                id="username"
                placeholder="username"
                required
                v-model="user.username"
              />
              <div class="invalid-feedback">email tidak boleh kosong!</div>
            </div>
          </div>
          <div class="form-group row">
            <label for="email" class="col-sm-2 col-form-label">Email</label>
            <div class="col-sm-10">
              <input
                type="email"
                class="form-control"
                id="email"
                placeholder="email"
                required
                v-model="user.email"
              />
              <div class="invalid-feedback">email tidak boleh kosong!</div>
            </div>
          </div>
          <div class="form-group row">
            <label for="role" class="col-sm-2 col-from-label">Role</label>
            <div class="col-sm-10">
              <select class="form-control" id="role" required v-model="user.role">
                <option value>Pilih Role</option>
                <option value="1">Admin</option>
                <option value="2">Manager</option>
              </select>
              <div class="invalid-feedback">role tidak boleh kosong!</div>
            </div>
          </div>
          <div class="form-group row" v-if="user.role != '1'">
            <label for="dept" class="col-sm-2 col-from-label">Departement</label>
            <div class="col-sm-10">
              <select
                class="form-control"
                id="dept"
                v-model="user.dept_id"
                :required="user.role != '1'"
              >
                <option value>Pilih Departement</option>
                <option value="1">Departement 1</option>
                <option value="2">Departement 2</option>
              </select>
              <div class="invalid-feedback">departement tidak boleh kosong!</div>
            </div>
          </div>
          <div class="form-group row">
            <label for="password" class="col-sm-2 col-form-label">Password</label>
            <div class="col-sm-10">
              <input
                type="password"
                class="form-control"
                id="password"
                placeholder="New Password"
                v-model="user.password"
              />
            </div>
            <div class="invalid-feedback">password tidak boleh kosong!</div>
          </div>
          <div class="form-group row">
            <label for class="col-sm-2 col-from-label"></label>
            <div class="col-sm-10">
              <button type="button" class="btn btn-primary" @click="confirmCreateUser()">Create</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      user: {
        username: "",
        email: "",
        role: "",
        dept_id: "",
        password: "",
      },
    };
  },
  methods: {
    confirmCreateUser() {
      if (
        (this.user.username.trim() == "" || this.user.email.trim() == "",
        this.user.role.trim() == "",
        this.user.password.trim() == "")
      ) {
        return;
      }
      if (this.user.role.trim() != "1" && this.user.dept_id == "") {
        return;
      }

      Swal.fire({
        title: "Apakah anda yakin?",
        text: "Anda akan membuat user baru",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Create",
      }).then((result) => {
        if (result.value) {
          this.createUser();
        }
      });
    },
    async createUser() {
      this.$Progress.start();
      try {
        let body = {
          username: this.user.username,
          email: this.user.email,
          role: this.user.role,
          dept_id: this.user.dept_id,
          password: this.user.password,
        };
        let endpoint = `${BASE_URL}/user`;
        let response = await axios.post(endpoint, body);

        if (response.status == 200) {
          this.clearData();
          Swal.fire("Berhasil!", "User berhasil ditambahkan.", "success");
          this.$Progress.finish();
        }
      } catch (error) {
        console.log(error);
        Swal.fire("Gagal!", "User gagal ditambahkan.", "error");
        this.$Progress.fail();
      }
    },
    clearData() {
      this.user.username = "";
      this.user.email = "";
      this.user.role = "";
      this.user.dept_id = "";
      this.user.password = "";
    },
    goBack() {
      this.$router.back();
    },
  },
};
</script>

<style>
</style>
