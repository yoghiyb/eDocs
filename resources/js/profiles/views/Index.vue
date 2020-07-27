<template>
  <div class="col-sm-12">
    <section class="content-header" style="margin-bottom: 25px;">
      <h1 class="pull-left">Profile</h1>
    </section>

    <div class="container-fluid">
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
      <div class="tab-content mt-3" id="myTabContent">
        <div
          class="tab-pane fade active show"
          id="profile"
          role="tabpanel"
          aria-labelledby="profile-tab"
        >
          <div class="card">
            <div class="card-body">
              <div v-if="!isEdit" class="row">
                <div class="col-md-4 col-sm-12">
                  <img
                    :src="[$root.$data.authUser.photo == 'profile.png' ? './img/' + $root.$data.authUser.photo : '/storage/images/' + $root.$data.authUser.photo]"
                    alt="Photo profile"
                    width="150px"
                  />
                </div>
                <div class="col-md-8 col-sm-12">
                  <div class="form-group">
                    <label for>Name</label>
                    <p>
                      {{user &&user.username }}
                      <span
                        class="badge"
                        :class="[user && user.role == '1' ? 'badge-success' : user.role == '2' ? 'badge-warning' : 'badge-primary']"
                      >{{user && user.role == '1' ? 'ADMIN' : user.role == '2' ? 'MANAGER' : 'STAFF' }}</span>
                      <span
                        v-if="user.dept_id != ''"
                        class="badge ml-1"
                        :class="[user && user.role == '1' ? 'badge-success' : user.role == '2' ? 'badge-warning' : 'badge-primary']"
                      >{{user && user.dept_id == '1' ? 'Departement 1' : 'Departement 2'}}</span>
                    </p>
                  </div>
                  <div class="form-group">
                    <label for>Email</label>
                    <p>{{user && user.email}}</p>
                  </div>
                  <button
                    type="button"
                    class="btn btn-sm btn-primary float-right"
                    @click="toogleEdit()"
                  >Edit</button>
                </div>
              </div>
              <form v-else class="needs-validation" novalidate>
                <div class="form-group row">
                  <label for="username" class="col-sm-2 col-form-label">Username</label>
                  <div class="col-sm-10">
                    <input
                      type="text"
                      class="form-control"
                      id="username"
                      placeholder="Username"
                      required
                      v-model="user.username"
                    />
                    <div class="invalid-feedback">username tidak boleh kosong!</div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="email" class="col-sm-2 col-form-label">Email</label>
                  <div class="col-sm-10">
                    <input
                      type="text"
                      class="form-control"
                      id="email"
                      placeholder="email"
                      required
                      v-model="user.email"
                    />
                    <div class="invalid-feedback">Email tidak boleh kosong!</div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="role" class="col-sm-2 col-from-label">Role</label>
                  <div class="col-sm-10">
                    <p
                      class="form-control"
                    >{{ user && user.id == '1' ? 'Admin' : user.id == '2' ? 'Manager' : 'Staff'}}</p>
                    <!-- <select class="form-control" id="role" required v-model="user.role" disabled>
                    <option value>Pilih Role</option>
                    <option value="1">Admin</option>
                    <option value="2">Manager</option>
                    <option value="3">Staff</option>
                  </select>
                    <div class="invalid-feedback">Role tidak boleh kosong!</div>-->
                  </div>
                </div>
                <div
                  class="form-group row"
                  v-if="user.role != '1' &&  $root.$data.authUser.role !== '3'"
                >
                  <label for="dept" class="col-sm-2 col-from-label">Departement</label>
                  <div class="col-sm-10">
                    <p
                      class="form-control"
                    >{{ user && user.dept == '1' ? 'Departement 1' : 'Departement 2' }}</p>
                    <!-- <select
                    class="form-control"
                    id="dept"
                    v-model="user.dept_id"
                    :required="user.role != '1'"
                    disabled
                  >
                    <option value>Pilih Departement</option>
                    <option value="1">Departement 1</option>
                    <option value="2">Departement 2</option>
                    </select>-->
                    <div class="invalid-feedback">departement tidak boleh kosong!</div>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-sm-2">
                    <img
                      :src="[user.photo == 'profile.png' ? './img/' + user.photo : newPhoto != '' ? newPhoto : './storage/images/' + user.photo ]"
                      alt="Photo profile"
                      width="150px"
                    />
                  </div>
                  <div class="col-sm-10">
                    <label for="photo">Photo</label>
                    <div class="custom-file">
                      <input
                        type="file"
                        class="custom-file-input"
                        id="photo"
                        required
                        @change="updatePhoto"
                        accept="image/*"
                      />
                      <label
                        class="custom-file-label"
                        for="photo"
                      >{{ user && user.photo && user.photo.name ? user.photo.name : 'Choose file...' }}</label>
                      <div class="invalid-feedback">Example invalid custom file feedback</div>
                    </div>
                    <button
                      type="button"
                      class="btn btn-primary mt-3"
                      @click="updateConfirmation"
                    >Update Profile</button>
                    <button
                      type="button"
                      class="btn btn-danger mt-3"
                      @click="toogleEdit()"
                    >Cancel Edit</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div
          class="tab-pane fade"
          id="change-password"
          role="tabpanel"
          aria-labelledby="change-password-tab"
        >
          <div class="card">
            <div class="card-body">
              <form>
                <div class="form-group row">
                  <label for="oldPassword" class="col-sm-2 col-form-label">Old Password</label>
                  <div class="col-sm-10">
                    <input
                      type="password"
                      class="form-control"
                      id="oldPassword"
                      placeholder="Confirm Password"
                      v-model="pass.old"
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
                    />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="dept" class="col-sm-2 col-from-label"></label>
                  <div class="col-sm-10">
                    <button
                      type="button"
                      class="btn btn-primary"
                      @click="confirmChangePassword"
                    >Change Password</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <!-- comment space -->
        <comment v-if="user && user.id" :owner="`user_${user.id}`" />
        <!-- end comment -->
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isEdit: false,
      user: {
        id: "",
        username: "",
        email: "",
        role: "",
        dept_id: "",
        photo: {
          name: "",
        },
      },
      newPhoto: "",
      pass: {
        old: "",
        new: "",
      },
    };
  },
  mounted() {
    // updateProfile();
    this.getProfile();
  },
  methods: {
    async getProfile() {
      try {
        let endpoint = `${BASE_URL}/user`;
        let response = await axios.get(endpoint);

        if (response.status === 200) {
          this.user = response.data;
          if (response.data.dept_id == null) {
            this.user.dept_id = "";
          }
          //   this.user.photo.name = response.data.photo;

          console.log(response.data);
        }
      } catch (error) {}
    },
    updateConfirmation() {
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
        text: "Anda tidak akan dapat mengembalikan ini!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Update",
      }).then((result) => {
        if (result.value) {
          this.updateProfile();
        }
      });
    },
    async updateProfile() {
      this.$Progress.start();
      let endpoint = `${BASE_URL}/user/${this.user.id}`;

      let formData = new FormData();
      formData.append("_method", "PUT");
      Object.keys(this.user).forEach((key, index) => {
        // console.log(key, this.user[key]);
        formData.append(key, this.user[key]);
      });
      //   for (var pair of formData.entries()) {
      //     console.log(pair[0] + ", " + pair[1]);
      //   }

      try {
        let headers = {
          "Content-Type": "multipart/form-data",
        };
        let response = await axios.post(endpoint, formData, { headers });

        if (response.status === 200) {
          this.getProfile();
          Swal.fire("Berhasil!", "Profile berhasil diperbaruhi.", "success");
          this.$Progress.finish();
        }
      } catch (error) {
        Swal.fire("Gagal!", "Profile gagal diperbaruhi.", "error");
        this.$Progress.fail();
      }
    },
    confirmChangePassword() {
      if (this.pass.old == "" || this.pass.new == "") {
        return;
      }

      Swal.fire({
        title: "Apakah anda yakin?",
        text: "Anda tidak akan dapat mengembalikan ini!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Udpate",
      }).then((result) => {
        if (result.value) {
          this.changePassword();
        }
      });
    },
    async changePassword() {
      this.$Progress.start();
      try {
        let endpoint = `${BASE_URL}/user/cp/${this.user.id}`;
        let response = await axios.patch(endpoint, this.pass);

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
    updatePhoto(e) {
      this.user.photo = e.target.files[0];
      this.newPhoto = URL.createObjectURL(e.target.files[0]);
      console.log(this.user.photo);
    },
    toogleEdit() {
      this.isEdit = !this.isEdit;
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
