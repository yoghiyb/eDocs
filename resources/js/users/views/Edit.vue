<template>
  <div class="col-sm-12">
    <section class="content-header" style="margin-bottom: 25px;">
      <div class="row">
        <h1 class="col-md-11">Edit User</h1>
        <button class="col-md-1 col-sm-2 btn btn-primary" @click="goBack()">Back</button>
      </div>
    </section>

    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-sm-12">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item">
                <a
                  class="nav-link active"
                  id="profile-tab"
                  data-toggle="tab"
                  href="#profile"
                  role="tab"
                  aria-controls="profile"
                  aria-selected="false"
                >Profile</a>
              </li>
              <li class="nav-item">
                <a
                  class="nav-link"
                  id="change-password-tab"
                  data-toggle="tab"
                  href="#change-password"
                  role="tab"
                  aria-controls="change-password"
                  aria-selected="false"
                >Change Password</a>
              </li>
            </ul>
          </div>
        </div>
        <div class="tab-content" id="myTabContent">
          <div
            class="tab-pane fade mt-3 active show"
            id="profile"
            role="tabpanel"
            aria-labelledby="profile-tab"
          >
            <div class="container-fluid">
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
                      <option v-if="$root.$data.authUser.role === '1'" value="1">Admin</option>
                      <option value="2">Manager</option>
                      <option value="3">Staff</option>
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
                  <label for class="col-sm-2 col-from-label"></label>
                  <div class="col-sm-10">
                    <button
                      type="button"
                      class="btn btn-primary"
                      @click="confirmUpdateUser()"
                    >Update</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <div
            class="tab-pane fade mt-3"
            id="change-password"
            role="tabpanel"
            aria-labelledby="change-password-tab"
          >
            <div class="container-fluid">
              <form>
                <div class="form-group row" v-if="$root.$data.authUser.role != '1'">
                  <label for="oldPassword" class="col-sm-2 col-form-label">Old Password</label>
                  <div class="col-sm-10">
                    <input
                      type="password"
                      class="form-control"
                      id="oldPassword"
                      placeholder="Confirm Password"
                      v-model="pass.old"
                      :required="$root.$data.authUser.role != '1'"
                    />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="newPassword" class="col-sm-2 col-form-label">New Password</label>
                  <div class="col-sm-10">
                    <input
                      type="password"
                      class="form-control"
                      id="newPassword"
                      placeholder="New Password"
                      v-model="pass.new"
                      required
                    />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="dept" class="col-sm-2 col-from-label"></label>
                  <div class="col-sm-10">
                    <button
                      type="button"
                      class="btn btn-primary"
                      @click="confirmChangePassword()"
                    >Change Password</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
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
      },
      pass: {
        old: "",
        new: "",
      },
    };
  },
  mounted() {
    this.getUser();
    console.log(this.$root.$data.authUser.role);
  },
  methods: {
    async getUser() {
      this.$Progress.start();
      try {
        let endpoint = `${BASE_URL}/user/${this.$route.params.id}/edit`;
        let response = await axios.get(endpoint);

        if (response.status === 200) {
          let { username, email, role, dept_id } = response.data;
          let data = { username, email, role, dept_id };
          this.user = data;
          if (response.data.dept_id == null) {
            this.user.dept_id = "";
          }
          this.user.photo = "";
          this.$Progress.finish();
        }
      } catch (error) {
        console.log(error);
        this.$Progress.fail();
      }
    },
    confirmUpdateUser() {
      if (
        this.user.username.trim() == "" ||
        this.user.role.trim() == "" ||
        this.user.email.trim() == ""
      ) {
        return;
      }

      if (this.user.role.trim() != "1" && this.user.dept_id == "") {
        return;
      }

      Swal.fire({
        title: "Apakah anda yakin?",
        text: "Anda akan memperbarui data user",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Update",
      }).then((result) => {
        if (result.value) {
          this.updateUser();
        }
      });
    },
    async updateUser() {
      this.$Progress.start();
      try {
        let endpoint = `${BASE_URL}/user/${this.$route.params.id}`;

        let formData = new FormData();
        formData.append("_method", "PUT");
        Object.keys(this.user).forEach((key, index) => {
          // console.log(key, this.user[key]);
          formData.append(key, this.user[key]);
        });

        let headers = {
          "Content-Type": "multipart/form-data",
        };

        let response = await axios.post(endpoint, formData, { headers });

        if (response.status === 200) {
          this.getUser();
          Swal.fire("Berhasil!", "Profile berhasil diperbaruhi.", "success");
          this.$Progress.finish();
        }
      } catch (error) {
        console.log(error);
        Swal.fire("Gagal!", "Profile gagal diperbaruhi.", "error");
        this.$Progress.fail();
      }
    },
    confirmChangePassword() {
      if (this.$root.$data.authUser.role != "1") {
        if (this.pass.old == "" || this.pass.new == "") {
          return;
        }
      }

      Swal.fire({
        title: "Apakah anda yakin?",
        text: "Anda tidak akan dapat mengembalikan ini!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Change",
      }).then((result) => {
        if (result.value) {
          this.changePassword();
        }
      });
    },
    async changePassword() {
      this.$Progress.start();
      try {
        let endpoint = `${BASE_URL}/user/cp/${this.$route.params.id}`;
        let body = {
          role: this.$root.$data.authUser.role,
          old: this.pass.old,
          new: this.pass.new,
        };
        let response = await axios.patch(endpoint, body);

        if (response.status === 200) {
          this.$Progress.finish();
          Swal.fire("Berhasil!", "Password berhasil diubah.", "success");
          this.pass.old = "";
          this.pass.new = "";
          this.$Progress.finish();
        }
      } catch (error) {
        console.log(error);
        Swal.fire("Gagal!", "Profile gagal diperbaruhi.", "error");
        this.$Progress.fail();
      }
    },
    goBack() {
      this.$router.back();
    },
  },
  watch: {
    "user.role": function (newVal, oldVal) {
      if (newVal == "1") {
        this.user.dept_id = "";
      }
    },
  },
};
</script>

<style>
</style>
