<template>
  <div class="col-sm-12">
    <section class="content-header" style="margin-bottom: 25px;">
      <div class="row">
        <h1 class="col-md-11">Upload New Document</h1>
        <button class="col-md-1 col-sm-2 btn btn-primary" @click="goBack()">Back</button>
      </div>
    </section>

    <div class="card">
      <div class="card-body">
        <form class="needs-validation" novalidate>
          <div class="row">
            <div :class="[$root.$data.authUser.role != '3' ? 'col-sm-6' : 'col-sm-12' ]">
              <div class="form-group">
                <label for="name" class="col-form-label">Name</label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  placeholder="name"
                  required
                  v-model="document.name"
                />
                <div class="invalid-feedback">email tidak boleh kosong!</div>
              </div>
            </div>
            <div
              v-if="$root.$data.authUser.role != '3'"
              :class="[$root.$data.authUser.role != '3' ? 'col-sm-6' : '']"
            >
              <div class="form-group">
                <label for="name" class="col-form-label">Status</label>
                <select
                  name="status"
                  id="status"
                  class="form-control"
                  :required="$root.$data.authUser.role != '3'"
                  v-model="document.status"
                >
                  <option value="PENDING">PENDING</option>
                  <option value="APPROVED">APPROVED</option>
                </select>
                <div class="invalid-feedback">email tidak boleh kosong!</div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="tags">Tag</label>
            <multiselect
              id="tags"
              v-model="tag.value"
              placeholder="Search tag"
              label="name"
              track-by="id"
              :options="tag.options"
              :multiple="true"
              :taggable="true"
            ></multiselect>
          </div>
          <div class="form-group">
            <label for="description">Description</label>
            <textarea
              name="description"
              id="description"
              cols="30"
              rows="10"
              class="form-control"
              v-model="document.description"
            ></textarea>
          </div>
          <div class="form-group">
            <label for="file">File</label>
            <div class="custom-file">
              <input
                type="file"
                class="custom-file-input"
                id="file"
                name="file"
                required
                accept=".xlsx, .xls, image/*, .doc, .docx, .pdf"
                @change="selectFile"
              />
              <label class="custom-file-label" for="file">Choose file...</label>
              <div class="invalid-feedback">file tidak boleh kosong</div>
            </div>
          </div>
          <button
            class="float-right btn btn-primary"
            type="button"
            @click="confirmUploadFile"
          >Upload</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      document: {
        name: "",
        status: "PENDING",
        description: "",
        file: "",
      },
      tag: {
        value: [],
        options: [],
      },
    };
  },
  mounted() {
    this.fetchTag();
  },
  methods: {
    fetchTag() {
      let endpoint = `${BASE_URL}/tag/all`;
      axios
        .get(endpoint)
        .then((response) => {
          if (response.status === 200) {
            this.tag.options = response.data;
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    confirmUploadFile() {
      if (
        this.document.name.trim() == "" ||
        this.document.description.trim() == "" ||
        this.document.file == "" ||
        this.tag.length < 1
      ) {
        return;
      }

      Swal.fire({
        title: "Apakah anda yakin?",
        text: "Anda akan upload file ini!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Upload",
      }).then((result) => {
        if (result.value) {
          this.uploadFile();
        }
      });
    },
    async uploadFile() {
      this.$Progress.start();
      let body = {
        ...this.document,
        tag_id: JSON.stringify(this.tag.value),
        created_by: this.$root.$data.authUser.id,
      };

      //   console.log(body);

      let formData = new FormData();

      Object.keys(body).forEach((key, index) => {
        // console.log(key, this.user[key]);
        formData.append(key, body[key]);
      });
      //   for (var pair of formData.entries()) {
      //     console.log(pair[0] + ", " + pair[1]);
      //   }

      try {
        let endpoint = `${BASE_URL}/document/upload`;
        let headers = {
          "Content-Type": "multipart/form-data",
        };
        let response = await axios.post(endpoint, formData, { headers });

        if (response.status === 200) {
          Swal.fire("Berhasil!", "File berhasil diupload.", "success");
          this.clearFormUpload();
          this.$Progress.finish();
        }
      } catch (error) {
        console.log(error);
        Swal.fire("Gagal!", "File gagal diupload.", "error");
        this.$Progress.fail();
      }
    },
    clearFormUpload() {
      this.document.name = "";
      this.document.status = "PENDING";
      this.document.description = "";
      this.document.file = "";
      this.tag.value = [];
    },
    selectFile(e) {
      this.document.file = e.target.files[0];
    },
    addTag(newTag) {
      const tag = {
        name: newTag,
        code: newTag.substring(0, 2) + Math.floor(Math.random() * 10000000),
      };
      this.options.push(tag);
      this.value.push(tag);
    },
    goBack() {
      this.$router.back();
    },
  },
};
</script>

<style>
</style>
