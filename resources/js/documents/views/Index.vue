<template>
  <div class="col-sm-12">
    <section class="content-header" style="margin-bottom: 25px;">
      <div class="row">
        <h1 class="col-md-11">Documents</h1>
        <button
          type="button"
          class="col-md-1 col-sm-2 btn btn-primary"
          @click="goToUploadDocument()"
        >Upload</button>
      </div>
    </section>

    <div class="row">
      <div class="col-sm-12">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a
              class="nav-link active"
              id="documents-tab"
              data-toggle="tab"
              href="#documents"
              role="tab"
              aria-controls="documents"
              aria-selected="false"
            >Documents</a>
          </li>
          <li class="nav-item">
            <a
              class="nav-link"
              id="my-documents-tab"
              data-toggle="tab"
              href="#my-documents"
              role="tab"
              aria-controls="my-documents"
              aria-selected="false"
            >My Documents</a>
          </li>
          <li class="nav-item" v-if="$root.$data.authUser.role != '3'">
            <a
              class="nav-link"
              id="verifications-tab"
              data-toggle="tab"
              href="#verifications"
              role="tab"
              aria-controls="verifications"
              aria-selected="false"
            >
              Verifications
              <span class="badge badge-danger">9</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
    <div class="tab-content" id="myTabContent">
      <div
        class="tab-pane fade mt-3 active show"
        id="documents"
        role="tabpanel"
        aria-labelledby="documents-tab"
      >
        <div class="container-fluid">
          <h1>DOCUMENTS</h1>
        </div>
      </div>
      <div
        class="tab-pane fade mt-3"
        id="my-documents"
        role="tabpanel"
        aria-labelledby="my-documents-tab"
      >
        <data-viewer
          :source="$root.$data.authUser.id != '' && `mydocument/${$root.$data.authUser.id}`"
          column="name"
          :hasAction="true"
          :canEdit="true"
          :canDelete="$root.$data.authUser.role !== '3' ? true : false"
          :showDetail="true"
          editPath="DocumentEdit"
          detailPath="DocumentDetail"
          :deleteSource="$root.$data.authUser.role !== '3' && 'document'"
        />
      </div>
      <div
        class="tab-pane fade mt-3"
        id="verifications"
        role="tabpanel"
        aria-labelledby="verifications-tab"
        v-if="$root.$data.authUser.role !== '3'"
      >
        <data-viewer
          :source="$root.$data.authUser.role === '3' ? null : 'pending'"
          column="name"
          :hasAction="true"
          :canEdit="true"
          :canDelete="$root.$data.authUser.role === '3' ? false : true"
          :showDetail="true"
          editPath="DocumentEdit"
          :detailPath="$root.$data.authUser.role !== '3' && 'DocumentDetail'"
          deleteSource="document"
        />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {};
  },
  methods: {
    goToUploadDocument() {
      this.$router.push({ name: "DocumentUpload" });
    },
  },
};
</script>

<style>
</style>
