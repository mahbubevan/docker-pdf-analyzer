const express = require('express')
const http = require('http')
const {Server} = require('socket.io')
const cors = require('cors')

const app = express()
const server = http.createServer(app)

const io = new Server(server,{
    cors:{
        origin:'*'
    }
})

app.use(cors())
app.use(express.json())

app.get('/',(req,res)=>{
    res.json({
        service:'PDF Analyzer Realtime Node Server',
        status: 'Running'
    })
})

app.get('/health',(req,res)=>{
    res.json({
        status:'ok'
    })
})

app.post('/event',(req,res)=>{
    const event = req.body 
    console.log('Event received', event);

    io.emit('pdf-progress',event)

    res.json({
        status:'Broadcasted',
        event
    })
    
})







io.on('connection',(socket)=>{
    console.log(`Browser connected: ${socket.id}`);
    

    socket.on('disconnect',()=>{
        console.log(`Browser disconnected: ${socket.id}`);
        
    })
})

const PORT = 3000
const HOST = "0.0.0.0" 

server.listen(PORT,HOST,()=>{
    console.log(`Realtime node service running on ${HOST}:${PORT}`);
    
})