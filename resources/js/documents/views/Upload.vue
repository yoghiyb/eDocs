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
                <input type="text" class="form-control" id="name" placeholder="name" required />
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
            <textarea name="description" id="description" cols="30" rows="10" class="form-control"></textarea>
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
              />
              <label class="custom-file-label" for="file">Choose file...</label>
              <div class="invalid-feedback">file tidak boleh kosong</div>
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
