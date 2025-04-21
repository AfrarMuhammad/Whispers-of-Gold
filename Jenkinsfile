pipeline {
    agent any
    environment {
        DOCKER_HOST = 'tcp://host.docker.internal:2375' // Connect to Docker Desktop daemon
    }
    stages {
        stage('Clone') {
            steps {
                git branch: 'main', url: 'https://github.com/AfrarMuhammad/Whispers-of-Gold.git'
            }
        }

        stage('Build Image') {
            steps {
                sh 'docker build -t my-wos-app .'
            }
        }

        stage('Run Container') {
            steps {
                sh 'docker-compose -f docker-compose.yml up -d --build'
            }
        }

      stage('Test') {
    steps {
        sh '''
            docker build -f Dockerfile.selenium -t selenium-runner .
            docker run --rm selenium-runner
        '''
    }
}


    }
}