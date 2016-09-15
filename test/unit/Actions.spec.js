import chai from 'chai';
const expect = chai.expect;
import sinonChai from 'sinon-chai';
import sinon from 'sinon';
import Vue from 'vue';
import VueRouter from 'vue-router';
import VueResource from 'vue-resource';
import * as actions from '../../resources/assets/js/vuex/actions';
import {router} from '../../resources/assets/js/app';
import Auth from '../../resources/assets/js/vuex/modules/auth';
chai.use(sinonChai);
Vue.use(VueResource);
Vue.use(VueRouter);

// helper for testing action with expected mutations
const testAction = (action, args, state, expectedMutations, done) => {
    let count = 0;
    // mock dispatch
    const dispatch = (name, ...payload) => {
        const mutation = expectedMutations[count];
        expect(mutation.name).to.equal(name);
        if (payload) {
            expect(mutation.payload).to.deep.equal(payload);
        }
        count++;
        if (count >= expectedMutations.length) {
            done();
        }
    };
    // call the action with mocked store and arguments
    action({dispatch, state}, ...args);

    // check if no mutations should have been dispatched
    if (expectedMutations.length === 0) {
        expect(count).to.equal(0);
        done();
    }
};

const creds = {
    email: 'test',
    password: 'test'
};

let authState = Auth.state;

describe('Actions', () => {

    describe('login', () => {
        
        beforeEach(() => {
            authState.authenticated = false;
            localStorage.removeItem('id_token');
        });

        it('should throw an error on unsuccesful login', done => {
            Vue.http.interceptors.push((request, next) => {
                var body = {error: 'something'};
                next(request.respondWith(body, {
                    status: 401
                }));
            });

            
            testAction(actions.login, [creds], authState, [
                { name: 'SET_AUTH', payload: [false] },
                { name: 'SET_AUTH_ERR', payload: ['something'] }
            ], done);

            Vue.http.interceptors.shift();
        });

        it('should log the user in', done => {
            Vue.http.interceptors.push((request, next) => {
                var body = {'token' : '1234'};
                next(request.respondWith(body, { status: 200 }));
            });

            testAction(actions.login, [creds], authState, [
                { name: 'SET_AUTH', payload: [true] },
                { name: 'SET_AUTH_ERR', payload: [false] }
            ], done);
            
            Vue.http.interceptors.shift();
        });

        it('should redirect the user', done => {
            Vue.http.interceptors.push((request, next) => {
                var body = {'token' : '1234'};
                next(request.respondWith(body, { status: 200 }));
            });

            const dispatch = function(){};
            sinon.spy(router, 'go');
            
            actions.login({dispatch}, creds, '/test');

            setTimeout(()=>{
                expect(router.go).to.have.been.called;
                done();
            }, 0);

            Vue.http.interceptors.shift();            
        });
        
    });

    it('setAuth', done => {

        testAction(actions.setAuth, [true], authState, [
            { name: 'SET_AUTH', payload: [true] }
        ], done);
        
    });

    it('setAuthErr', done => {
        testAction(actions.setAuthErr, ['some error'], authState,[
            { name: 'SET_AUTH_ERR', payload: ['some error'] }
        ], done);
    });
    
});