<template>
  <div class="col-sm-12">
    <section class="content-header" style="margin-bottom: 25px;">
      <div class="row">
        <h1 class="col-md-11">Detail Document</h1>
        <button class="col-md-1 col-sm-2 btn btn-primary" @click="goBack()">Back</button>
      </div>
    </section>
    <div class="card">
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-4">
            <img src="/img/profile.png" width="200px" alt class="border" />
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="name">Name</label>
              <p id="name">{{ document && document.name ? document.name : '-' }}</p>
            </div>
            <div class="form-group">
              <label for="status">Status</label>
              <p>
                <span
                  v-if="document && document.status"
                  id="status"
                  class="badge"
                  :class="[document && document.status == 'APPROVED' ? 'badge-success' :'badge-danger']"
                >{{ document && document.status ? document.status : '-' }}</span>
                <span id="status" v-else>-</span>
              </p>
            </div>
            <div class="form-group">
              <label for="created_by">Created By</label>
              <p id="created_by">{{ document && document.user ? document.user.username : '-' }}</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="created_at">Created At</label>
              <p id="created_at">{{ document && document.created_at ? document.created_at : '-' }}</p>
            </div>
            <div class="form-group">
              <label for="created_at">Approved At</label>
              <p id="created_at">{{document && document.approved_at ? document.approved_at : '-' }}</p>
            </div>
            <div class="form-group">
              <label for="created_at">Approved By</label>
              <p id="created_at">
                {{document && document.approved_by_user && document.approved_by_user.username ? document.approved_by_user.username : '-' }}
                <span
                  v-if="document && document.approved_by_user"
                  class="badge badge-success"
                >{{document && document.approved_by_user && document.approved_by_user.role && document.approved_by_user.role == '1' ? 'ADMIN' : 'MANAGER' }}</span>
              </p>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label for="tags">Tag</label>
          <p>
            <span v-if="document && document.documents_tags.length < 1">-</span>
            <span
              v-else
              class="badge badge-success mr-1"
              v-for="(item,index) in document.documents_tags"
              :key="index"
            >{{item.tag.name}}</span>
          </p>
        </div>
        <div class="form-group">
          <label for="description">Description</label>
          <p>{{ document && document.description }}</p>
        </div>
        <button
          v-if="$root.$data.authUser.role != '3'"
          type="button"
          class="btn btn-sm btn-danger float-right"
          @click="confirmDeleteFile()"
        >Delete</button>
        <button
          type="button"
          class="btn btn-sm btn-primary float-right mr-2"
          @click="goToEdit()"
        >Edit</button>
        <button
          v-if="$root.$data.authUser.role != '3' &&  document.status == 'PENDING'"
          class="btn btn-sm btn-success float-right mr-2"
          @click="confirmApprove()"
        >Approve</button>
      </div>
    </div>
    <!-- comment space -->
    <comment :owner="`doc_${this.$route.params.id}`" />
    <!-- end comment -->
  </div>
</template>

<script>
export default {
  data() {
    return {
      document: "",
    };
  },
  mounted() {
    this.fetchDocumentDetail();
  },
  methods: {
    async fetchDocumentDetail() {
      this.$Progress.start();
      try {
        let endpoint = `${BASE_URL}/document/${this.$route.params.id}`;
        let response = await axios.get(endpoint);

        if (response.status == 200 && response.data) {
          this.document = response.data;
          this.$Progress.finish();
        }
      } catch (error) {
        console.log(error);
        this.$Progress.fail();
      }
    },
    confirmDeleteFile() {
      Swal.fire({
        title: "Apakah anda yakin?",
        text: "Anda tidak akan dapat mengembalikan ini!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Delete",
      }).then((result) => {
        if (result.value) {
          this.deleteFile();
        }
      });
    },
    async deleteFile() {
      this.$Progress.start();
      if (this.$root.$data.authUser.role == "3") {
        Swal.fire(
          "Gagal!",
          "Anda tidak mempunyai hak untuk menghapus.",
          "error"
        );
        this.$Progress.fail();
        return;
      }

      try {
        let endpoint = `${BASE_URL}/document/${this.$route.params.id}`;
        let response = await axios.delete(endpoint);

        if (response.status === 200) {
          this.$Progress.finish();
          this.goBack();
          Swal.fire("Berhasil!", "File berhasil dihapus.", "success");
        }
      } catch (error) {
        console.log(error);
        this.$Progress.fail();
      }
    },
    confirmApprove() {
      Swal.fire({
        title: "Apakah anda yakin?",
        text: "Anda akan menyetujui file ini!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Approve",
      }).then((result) => {
        if (result.value) {
          this.approveFile();
        }
      });
    },
    async approveFile() {
      this.$Progress.start();
      if (this.$root.$data.authUser.role == "3") {
        Swal.fire(
          "Gagal!",
          "Anda tidak mempunyai hak untuk menghapus.",
          "error"
        );
        this.$Progress.fail();
        return;
      }

      try {
        let endpoint = `${BASE_URL}/document/approve/${this.$route.params.id}`;

        let body = {
          status: "APPROVED",
          approved_by: parseInt(this.$root.$data.authUser.id),
        };

        let response = await axios.patch(endpoint, body);

        if (response.status == 200) {
          this.fetchDocumentDetail();
          Swal.fire("Berhasil!", "File berhasil setujui.", "success");
          this.$Progress.finish();
        }
      } catch (error) {
        console.log(error);
        Swal.fire("Gagal!", "File gagal disetujui.", "error");
        this.$Progress.fail();
      }
    },
    goBack() {
      this.$router.back();
    },
    goToEdit() {
      this.$router.push({
        name: "DocumentEdit",
        params: { id: this.$route.params.id },
      });
    },
  },
};
</script>

<style>
</style>
