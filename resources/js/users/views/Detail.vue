<template>
  <div class="col-sm-12">
    <section class="content-header" style="margin-bottom: 25px;">
      <div class="row">
        <h1 class="col-md-11">Detail User</h1>
        <button class="col-md-1 col-sm-2 btn btn-primary" @click="goBack()">Back</button>
      </div>
    </section>

    <div v-if="user.id != null" class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <img
              :src="[user && user.photo == 'profile.png' ? '/img/'+user.photo : '/storage/images/' + user.photo]"
              width="150"
              alt="User Image"
            />
          </div>
          <div class="col-md-8">
            <div class="form-group">
              <label for>Name</label>
              <p>
                {{user &&user.username }}
                <span
                  class="badge"
                  :class="[user && user.role == '1' ? 'badge-success' : user.role == '2' ? 'badge-warning' : 'badge-primary']"
                >{{user && user.role == '1' ? 'ADMIN' : user.role == '2' ? 'MANAGER' : 'STAFF' }}</span>
                <span
                  v-if="user.dept_id != null"
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
              v-if="$root.$data.authUser.role === '1'"
              type="button"
              class="btn btn-sm btn-primary float-right"
              @click="goToEdit()"
            >Edit</button>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="card">
      <div class="card-body">
        <h1 class="text-center">User kosong atau sudah dihapus!</h1>
      </div>
    </div>

    <!-- comment space -->
    <comment v-if="user.id != null" :owner="`user_${this.$route.params.id}`" />
    <!-- end comment -->
  </div>
</template>

<script>
export default {
  data() {
    return {
      user: "",
    };
  },
  mounted() {
    this.getUser();
  },
  methods: {
    async getUser() {
      this.$Progress.start();
      try {
        let endpoint = `${BASE_URL}/user/${this.$route.params.id}/edit`;
        let response = await axios.get(endpoint);

        if (response.status === 200) {
          //   let { username, email, role, dept_id } = response.data;
          //   let data = { username, email, role, dept_id };
          this.user = response.data;
          //   if (response.data.dept_id == null) {
          //     this.user.dept_id = "";
          //   }

          this.$Progress.finish();
        }
      } catch (error) {
        console.log(error);
        this.$Progress.fail();
      }
    },
    goBack() {
      this.$router.back();
    },
    goToEdit() {
      this.$router.push({
        name: "UserEdit",
        params: { id: this.$route.params.id },
      });
    },
  },
};
</script>

<style>
</style>
