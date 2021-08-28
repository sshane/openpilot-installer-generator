#include <time.h>
#include <unistd.h>

#include <cstdlib>
#include <fstream>
#include <map>

#include <QDebug>
#include <QDir>
#include <QTimer>
#include <QVBoxLayout>

#include "selfdrive/ui/qt/util.h"
#include "selfdrive/ui/qt/qt_window.h"
#include "selfdrive/ui/qt/setup/installer.h"

#define GIT_URL "https://github.com/27182818284590452353602874713526624977572470936999595"  // max 39 + 14 chars for "/openpilot.git"
#define BRANCH "161803398874989484820458683436563811772030917980576286213544862270526046281890244970720720418939113748475408807538689175212663386222353693179318006076672635443338908659593958290563832266131992829026788067520876689250171169620703222104321626954862629631361"  // max 255 chars
#define LOADING_MSG "314159265358979323846264338327950288419"  // max 39 chars
#define GIT_SSH_URL "git@github.com:commaai/openpilot.git"

#define CONTINUE_PATH "/data/continue.sh"

#define CACHE_PATH "/usr/comma/openpilot"
#define INSTALL_PATH "/data/openpilot"
#define TMP_INSTALL_PATH "/data/tmppilot"

extern const uint8_t str_continue[] asm("_binary_installer_continue_" BRAND "_sh_start");
extern const uint8_t str_continue_end[] asm("_binary_installer_continue_" BRAND "_sh_end");

bool time_valid() {
  time_t rawtime;
  time(&rawtime);

  struct tm * sys_time = gmtime(&rawtime);
  return (1900 + sys_time->tm_year) >= 2020;
}

void run(const char* cmd) {
  int err = std::system(cmd);
  assert(err == 0);
}

Installer::Installer(QWidget *parent) : QWidget(parent) {
  QVBoxLayout *layout = new QVBoxLayout(this);
  layout->setContentsMargins(150, 290, 150, 150);
  layout->setSpacing(0);

  title = new QLabel("Installing " LOADING_MSG);
  title->setStyleSheet("font-size: 90px; font-weight: 600;");
  layout->addWidget(title, 0, Qt::AlignTop);

  layout->addSpacing(170);

  bar = new QProgressBar();
  bar->setRange(0, 100);
  bar->setTextVisible(false);
  bar->setFixedHeight(72);
  layout->addWidget(bar, 0, Qt::AlignTop);

  layout->addSpacing(30);

  val = new QLabel("0%");
  val->setStyleSheet("font-size: 70px; font-weight: 300;");
  layout->addWidget(val, 0, Qt::AlignTop);

  layout->addStretch();

  QObject::connect(&proc, QOverload<int, QProcess::ExitStatus>::of(&QProcess::finished), this, &Installer::cloneFinished);
  QObject::connect(&proc, &QProcess::readyReadStandardError, this, &Installer::readProgress);

  QTimer::singleShot(100, this, &Installer::doInstall);

  setStyleSheet(R"(
    * {
      font-family: Inter;
      color: white;
      background-color: black;
    }
    QProgressBar {
      border: none;
      background-color: #292929;
    }
  )");
}

float lerp(float a, float b, float f) {
  return (a * (1.0 - f)) + (b * f);
}

void Installer::updateProgress(int percent) {
  int h = (int)(lerp(233, 360 + 131, percent / 100.)) % 360;
  int s = lerp(78, 62, percent / 100.);
  int b = lerp(94, 87, percent / 100.);

  bar->setValue(percent);
  bar->setStyleSheet(QString(R"(
    QProgressBar::chunk {
      background-color: hsb(%1, %2%, %3%);
    })").arg(h).arg(s).arg(b));
  val->setText(QString("%1%").arg(percent));
  update();
}

void Installer::doInstall() {
  // wait for valid time
  while (!time_valid()) {
    usleep(500 * 1000);
    qDebug() << "Waiting for valid time";
  }

  // cleanup
  run("rm -rf " TMP_INSTALL_PATH " " INSTALL_PATH);

  // do the install
  if (QDir(CACHE_PATH).exists()) {
    cachedFetch();
  } else {
    freshClone();
  }
}

void Installer::freshClone() {
  qDebug() << "Doing fresh clone";
  proc.start("git", {"clone", "--progress", GIT_URL, "-b", BRANCH,
                     "--depth=1", "--recurse-submodules", TMP_INSTALL_PATH});
}

void Installer::cachedFetch() {
  qDebug() << "Fetching with cache";

  run("cp -rp " CACHE_PATH " " TMP_INSTALL_PATH);
  int err = chdir(TMP_INSTALL_PATH);
  assert(err == 0);
  run("git remote set-branches --add origin " BRANCH);

  updateProgress(10);

  proc.setWorkingDirectory(TMP_INSTALL_PATH);
  proc.start("git", {"fetch", "--progress", "origin", BRANCH});
}

void Installer::readProgress() {
  const QVector<QPair<QString, int>> stages = {
    // prefix, weight in percentage
    {"Receiving objects: ", 95},
    {"Filtering content: ", 5},
  };

  auto line = QString(proc.readAllStandardError());

  int base = 0;
  for (const QPair kv : stages) {
    if (line.startsWith(kv.first)) {
      auto perc = line.split(kv.first)[1].split("%")[0];
      int p = base + int(perc.toFloat() / 100. * kv.second);
      updateProgress(p);
      break;
    }
    base += kv.second;
  }
}

void Installer::cloneFinished(int exitCode, QProcess::ExitStatus exitStatus) {
  qDebug() << "git finished with " << exitCode;
  assert(exitCode == 0);

  // some confirmation
  title->setText("Installation complete");

  updateProgress(100);

  // ensure correct branch is checked out
  int err = chdir(TMP_INSTALL_PATH);
  assert(err == 0);
  run("git checkout " BRANCH);
  run("git reset --hard origin/" BRANCH);

  // move into place
  run("mv " TMP_INSTALL_PATH " " INSTALL_PATH);

#ifdef INTERNAL
  run("mkdir -p /data/params/d/");

  std::map<std::string, std::string> params = {
    {"SshEnabled", "1"},
    {"RecordFrontLock", "1"},
    {"GithubSshKeys", SSH_KEYS},
  };
  for (const auto& [key, value] : params) {
    std::ofstream param;
    param.open("/data/params/d/" + key);
    param << value;
    param.close();
  }
  run("cd " INSTALL_PATH " && git remote set-url origin --push " GIT_SSH_URL);
#endif

  // write continue.sh
  FILE *of = fopen("/data/continue.sh.new", "wb");
  assert(of != NULL);

  size_t num = str_continue_end - str_continue;
  size_t num_written = fwrite(str_continue, 1, num, of);
  assert(num == num_written);
  fclose(of);

  run("chmod +x /data/continue.sh.new");
  run("mv /data/continue.sh.new " CONTINUE_PATH);

  // wait for the installed software's UI to take over
  QTimer::singleShot(60 * 1000, &QCoreApplication::quit);
}

int main(int argc, char *argv[]) {
  initApp();
  QApplication a(argc, argv);
  Installer installer;
  setMainWindow(&installer);
  return a.exec();
}
