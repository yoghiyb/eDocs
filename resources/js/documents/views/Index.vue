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
              <span v-if="totalPending > 0" class="badge badge-danger">{{totalPending}}</span>
            </a>
          </li>
          <li class="nav-item" v-if="$root.$data.authUser.role != '3'">
            <a
              class="nav-link"
              id="logs-tab"
              data-toggle="tab"
              href="#logs"
              role="tab"
              aria-controls="logs"
              aria-selected="false"
            >Logs</a>
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
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <div class="row">
                  <div class="col-md-1">
                    <h4>Search</h4>
                  </div>
                  <div class="col-md-2">
                    <select class="custom-select" v-model="query.search_column">
                      <option
                        v-for="(column,index) in columns"
                        :key="index"
                        :value="column"
                      >{{ column }}</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <select class="custom-select" v-model="query.search_operator">
                      <option v-for="(val, key) in operators" :key="key" :value="key">{{val}}</option>
                    </select>
                  </div>
                  <div class="col-md-7">
                    <div class="input-group mb-3">
                      <input
                        type="text"
                        class="form-control"
                        placeholder="Search"
                        aria-label="search"
                        aria-describedby="basic-addon2"
                        v-model="query.search_input"
                        @keyup.enter="fetchDocuments()"
                      />
                      <div class="input-group-append">
                        <button
                          class="btn btn-outline-secondary"
                          type="button"
                          @click="fetchDocuments()"
                        >Search</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div v-for="(item, index) in model.data" :key="index" class="col-sm-6 col-md-3">
            <div class="card">
              <div class="card-body cursor-pointer" @click="goToDetail(item.id)">
                <span>{{item.name}}</span>
                <span class="float-right">
                  <i class="fas" :class="getFileType(item.file)"></i>
                </span>
                <br />
                <span class="badge badge-success">{{ item.status }}</span>
                <p>
                  Created By : {{item.user.username}}
                  <span
                    class="badge"
                    :class="[item.user.role == '1' ? 'badge-success' : item.user.role == '2' ? 'badge-warning' : 'badge-primary']"
                  >{{item.user.role == '1' ? 'ADMIN' : item.user.role == '2' ? 'MANAGER' : 'STAFF'}}</span>
                </p>
                <p>Created At : {{ item.created_at }}</p>
                <p>
                  Approved By : {{ item.approved_by_user.username }}
                  <span
                    class="badge"
                    :class="[item.approved_by_user.role == '1' ? 'badge-success' : item.approved_by_user.role == '2' ? 'badge-warning' : 'badge-primary']"
                  >{{item.approved_by_user.role == '1' ? 'ADMIN' : item.approved_by_user.role == '2' ? 'MANAGER' : 'STAFF'}}</span>
                </p>
                <span
                  class="mr-1 badge badge-success"
                  v-for="(tag_item, index) in item.documents_tags"
                  :key="index"
                >{{tag_item.tag.name}}</span>
              </div>
              <div class="card-footer">
                <button type="button" class="btn btn-secondary btn-sm" @click="downloadFile(item)">
                  <i class="fas fa-download"></i>
                </button>
                <button
                  type="button"
                  class="btn btn-secondary btn-sm"
                  @click="downloadFile(item, 'pdf')"
                >
                  <i class="fas fa-file-pdf"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-footer">
                <div class="row">
                  <div class="col-md-6">
                    <div class="row">
                      <div class="col-md-6">
                        <span>Displaying {{model.from}} - {{model.to}} of {{model.total}} rows</span>
                      </div>
                    </div>
                    <div class="d-flex flex-row">
                      <span class="mr-1">Rows per page</span>
                      <input
                        type="text"
                        v-model="query.per_page"
                        class="form-control form-control-sm"
                        style="max-width: 10%"
                        @keyup.enter="fetchDocuments()"
                      />
                    </div>
                  </div>
                  <div class="col-md-6">
                    <nav class="float-right">
                      <ul class="pagination pagination-sm">
                        <li class="page-item">
                          <button
                            class="page-link"
                            aria-label="Previous"
                            @click="prev()"
                            :disabled="!model.prev_page_url"
                          >
                            <span aria-hidden="true">&laquo;</span>
                            <span class="sr-only">Previous</span>
                          </button>
                        </li>
                        <li class="page-item" v-for="page in model.last_page" :key="page">
                          <button class="page-link" @click="goToPage(page)">{{page}}</button>
                        </li>
                        <li class="page-item">
                          <button
                            class="page-link"
                            aria-label="Next"
                            @click="next()"
                            :disabled="!model.next_page_url"
                          >
                            <span aria-hidden="true">&raquo;</span>
                            <span class="sr-only">Next</span>
                          </button>
                        </li>
                      </ul>
                    </nav>
                  </div>
                </div>
              </div>
            </div>
          </div>
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
      <div
        class="tab-pane fade mt-3"
        id="logs"
        role="tabpanel"
        aria-labelledby="logs-tab"
        v-if="$root.$data.authUser.role !== '3'"
      >
        <div class="row">
          <div class="col-md-12"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      totalPending: 0,
      model: {},
      columns: {},
      query: {
        page: 1,
        column: "name",
        direction: "desc",
        per_page: 10,
        search_column: "name",
        search_operator: "equal",
        search_input: "",
      },
      operators: {
        equal: "=",
        not_equal: "!=",
        less_than: "<",
        greather_than: ">",
        less_than_or_equal_to: "<=",
        greater_than_or_equal_to: ">=",
        in: "IN",
        like: "LIKE",
      },
    };
  },
  mounted() {
    this.fetchDocuments();
    console.log(this.$root.$data.authUser.role != "3");
    if (this.$root.$data.authUser.role != "3") {
      this.getTotalPendingDocuments();
    }
  },
  methods: {
    goToUploadDocument() {
      this.$router.push({ name: "DocumentUpload" });
    },
    fetchDocuments() {
      this.$Progress.start();
      if (this.query.per_page == "") this.query.per_page = 10;
      let endpoint = `${BASE_URL}/documents?column=${this.query.column}&direction=${this.query.direction}&page=${this.query.page}&per_page=${this.query.per_page}&search_column=${this.query.search_column}&search_operator=${this.query.search_operator}&search_input=${this.query.search_input}`;
      axios
        .get(endpoint)
        .then((response) => {
          if (response.status === 200) {
            this.model = response.data.model;
            this.columns = response.data.columns;
            this.$Progress.finish();
          }
        })
        .catch((response) => {
          console.log(response);
          this.$Progress.fail();
        });
    },
    goToDetail(id) {
      this.$router.push({
        name: "DocumentDetail",
        params: { id },
      });
    },
    getFileType(file) {
      let fileType = file.split(".")[1];
      if (fileType == "jpeg" || fileType == "jpg" || fileType == "png") {
        return "fa-file-image";
      }
      if (fileType == "pdf") {
        return "fa-file-pdf";
      }
      if (fileType == "doc" || fileType == "docx") {
        return "fa-file-word";
      }
      if (fileType == "xls" || fileType == "xlsx") {
        return "fa-file-excel";
      }
    },
    next() {
      if (this.model.next_page_url) {
        this.query.page++;
        this.fetchDocuments();
      }
    },
    prev() {
      if (this.model.prev_page_url) {
        this.query.page--;
        this.fetchDocuments();
      }
    },
    goToPage(page) {
      this.query.page = page;
      this.fetchDocuments();
    },
    downloadFile(file, type = null) {
      this.$Progress.start();
      let endpoint = `${BASE_URL}/document/download/${file.file}`;
      axios
        .get(endpoint, { responseType: "blob" })
        .then((response) => {
          const mime = file.file.split(".")[1];
          var blob;

          if (type != "pdf") {
            blob = new Blob([response.data]);
          } else {
            blob = new Blob([response.data], { type: "application/pdf" });
          }

          const url = window.URL.createObjectURL(blob);
          const link = document.createElement("a");

          link.download =
            type == "pdf" ? `${file.name}.pdf` : `${file.name}.${mime}`;
          // link.download = `file.pdf`;
          link.href = url;

          link.click();

          this.$Progress.finish();
        })
        .catch((error) => {
          console.log(error);
          this.$Progress.fail();
        });
    },
    async getTotalPendingDocuments() {
      try {
        let endpoint = `${BASE_URL}/pending/total`;
        let response = await axios.get(endpoint);

        if (response.status == 200) {
          this.totalPending = response.data;
        }
      } catch (error) {
        console.log(error);
      }
    },
  },
};
</script>

<style scoped>
p {
  margin-bottom: 0;
}
.cursor-pointer {
  cursor: pointer;
}
</style>
