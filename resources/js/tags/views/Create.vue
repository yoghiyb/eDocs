<template>
  <div class="col-sm-12">
    <section class="content-header" style="margin-bottom: 25px;">
      <h1>Create New Tag</h1>
    </section>

    <div class="card">
      <div class="card-body">
        <form class="needs-validation" novalidate>
          <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">Tag Name</label>
            <div class="col-sm-10">
              <input
                type="text"
                class="form-control"
                id="name"
                placeholder="name"
                required
                v-model="name"
              />
              <div class="invalid-feedback">nama tag tidak boleh kosong!</div>
            </div>
          </div>
          <div class="form-group row">
            <label for class="col-sm-2 col-from-label"></label>
            <div class="col-sm-10">
              <button type="button" class="btn btn-primary" @click="confirmCreateTag()">Create</button>
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
      name: "",
    };
  },
  methods: {
    confirmCreateTag() {
      if (this.name == "") return;

      Swal.fire({
        title: "Apakah anda yakin?",
        text: "Anda akan membuat tag baru",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Create",
      }).then((result) => {
        if (result.value) {
          this.createTag();
        }
      });
    },
    async createTag() {
      this.$Progress.start();
      try {
        let endpoint = `${BASE_URL}/tag`;
        let response = await axios.post(endpoint, { name: this.name });

        if (response.status === 200) {
          Swal.fire("Berhasil!", "Tag berhasil ditambahkan.", "success");
          this.$Progress.finish();
          this.goToIndexTag();
        }
      } catch (error) {
        console.log(error);
        Swal.fire("Gagal!", "Tag gagal ditambahkan.", "error");
        this.$Progress.fail();
      }
    },
    goToIndexTag() {
      this.$router.back();
    },
  },
};
</script>

<style>
</style>
